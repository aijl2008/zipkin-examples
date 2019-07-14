package net.artron;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.cloud.client.loadbalancer.LoadBalanced;
import org.springframework.context.annotation.Bean;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.client.RestTemplate;

import javax.servlet.http.HttpServletResponse;
import java.util.Map;

@SpringBootApplication
@RestController
public class DemoZipin1Application {

    @Autowired
    private RestTemplate restTemplate;

    @GetMapping("/")
    public String demo(
            HttpServletResponse rsp,
            @RequestHeader Map<String, String> headers,
            @RequestParam(name = "nextUrl", defaultValue = "http://localhost:7012/") String nextUrl
    ) {
        headers.forEach((key, value) -> {
            System.out.println(String.format("Header '%s' = %s", key, value));
        });
        System.out.println("request " + nextUrl);
        return this.restTemplate.getForObject(nextUrl, String.class);
    }

    public static void main(String[] args) {
        SpringApplication.run(DemoZipin1Application.class, args);
    }

    @Bean
    @LoadBalanced
    public RestTemplate restTemplate() {
        return new RestTemplate();
    }
}
