# 安装zipkin

```
docker run -d -p 9411:9411 --name zipkin zipkin
```

# 添加host条目

```
echo "127.0.0.1 zipkin" >> /etc/hosts
```

# 测试 Java 的跟踪

## 启动 Java 环境

```
mvn clean package
java -jar java_endpoint_c/target/java_endpoint_c-0.0.1-SNAPSHOT
java -jar java_endpoint_b/target/java_endpoint_b-0.0.1-SNAPSHOT
java -jar java_endpoint_a/target/java_endpoint_a-0.0.1-SNAPSHOT
```

## 测试

```
curl http://localhost:7011/
```

# 测试PHP的跟踪

## 启动 PHP 环境

```
php -S 0.0.0.0:8013 php_endpoint_c/php_endpoint_c.php
php -S 0.0.0.0:8012 php_endpoint_b/php_endpoint_b.php
php -S 0.0.0.0:8011 php_endpoint_a/php_endpoint_a.php
```

## 测试

```
curl http://localhost:8011/
```

# 测试 NODE 的跟踪

## 启动 PHP 环境

```
cd node_endpoint_c && node node_endpoint_c.js
cd node_endpoint_b && node node_endpoint_b.js
cd node_endpoint_a && node node_endpoint_a.js

```

## 测试

```
curl http://localhost:8021/
```

# 测试 php 请求 node 
```
php -S 0.0.0.0:8014 php_endpoint_1/php_endpoint_1.php
curl "http://localhost:8014/?nextUrl=http://localhost:8022"
```

# 测试 php 请求 java 
```
php -S 0.0.0.0:8014 php_endpoint_1/php_endpoint_1.php
curl "http://localhost:8014/?nextUrl=http://localhost:7011"
```

# 测试 node 请求 php 
```
curl "http://localhost:8021/?nextUrl=http://localhost:8012"
```

# 测试 node 请求 java 
```
curl "http://localhost:8021/?nextUrl=http://localhost:7011"
```

# 测试 java 请求 php 
```
cd java_endpoint_1 && mvn clean package
java -jar java_endpoint_1/target/java_endpoint_1-0.0.1-SNAPSHOT
curl "http://localhost:7014/?nextUrl=http://localhost:8012"
```

# 测试 java 请求 node
```
cd java_endpoint_1 && mvn clean package
java -jar java_endpoint_1/target/java_endpoint_1-0.0.1-SNAPSHOT
curl "http://localhost:7014/?nextUrl=http://localhost:8022"
```