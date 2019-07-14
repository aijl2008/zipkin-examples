// initialize tracer
const process = require('process');
const express = require('express');
const CLSContext = require('zipkin-context-cls');
const {Tracer} = require('zipkin');
const {recorder} = require('./recorder');

const ctxImpl = new CLSContext('zipkin');
const localServiceName = 'node_endpoint_c';
const tracer = new Tracer({ctxImpl, recorder, localServiceName});

const app = express();

// instrument the server
const zipkinMiddleware = require('zipkin-instrumentation-express').expressMiddleware;
app.use(zipkinMiddleware({tracer}));


app.get('/', (req, res) => {
    let content = "<pre>";
    for (let key in req.headers) {
        content += "[" + process.pid + "]" + key + ":" + req.headers[key] + "\n";
    }
    content += "</pre>";
    res.send(content);
});


app.listen(8023, (s) => {
    console.log("启动成功，进程ID:" + process.pid);
});
