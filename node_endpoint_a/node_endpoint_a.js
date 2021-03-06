/* eslint-disable import/newline-after-import */
// initialize tracer
const rest = require('rest');
const axios = require('axios');
const express = require('express');
const CLSContext = require('zipkin-context-cls');
const {Tracer} = require('zipkin');
const {recorder} = require('./recorder');

const ctxImpl = new CLSContext('zipkin');
const localServiceName = 'node_endpoint_a';
const tracer = new Tracer({ctxImpl, recorder, localServiceName});

const app = express();

// instrument the server
const zipkinMiddleware = require('zipkin-instrumentation-express').expressMiddleware;
app.use(zipkinMiddleware({tracer}));

// instrument the client
const {restInterceptor} = require('zipkin-instrumentation-cujojs-rest');
const zipkinRest = rest.wrap(restInterceptor, {tracer});


app.get('/', (req, res) => {
    let content = "<pre>";
    for (let key in req.headers) {
        content += "[" + process.pid + "]" + key + ":" + req.headers[key] + "\n";
    }
    let nextUrl = req.query.nextUrl || "http://localhost:8022";
    content += "请求 " + nextUrl + "\n";

    let result = tracer.local('request b', () =>
        zipkinRest(nextUrl)
            .then(
                (response) => {
                    console.log(response.entity);
                    res.send(content + response.entity);
                    content = response.entity;
                }
            )
            .catch(err => console.error('Error', err.stack))
    );
});


app.listen(8021, (s) => {
    console.log("启动成功，进程ID:" + process.pid);
});
