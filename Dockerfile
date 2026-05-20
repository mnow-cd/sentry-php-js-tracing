FROM php:8.5-cli

RUN apt-get update && apt-get install -y git zip
RUN curl -L https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie
RUN chmod +x /usr/local/bin/pie
RUN pie install open-telemetry/ext-opentelemetry

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer