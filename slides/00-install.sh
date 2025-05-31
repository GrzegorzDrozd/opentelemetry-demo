#!/bin/bash

php -f composer.phar require \
open-telemetry/transport-grpc \
open-telemetry/exporter-otlp \
open-telemetry/sem-conv \
open-telemetry/opentelemetry-auto-symfony \
open-telemetry/opentelemetry-auto-pdo \
open-telemetry/opentelemetry-auto-io \
open-telemetry/opentelemetry-auto-psr18 \
open-telemetry/opentelemetry-auto-psr15 \
open-telemetry/opentelemetry-propagation-traceresponse \
open-telemetry/opentelemetry-auto-psr6 \
open-telemetry/opentelemetry-auto-psr3 \
open-telemetry/opentelemetry-auto-psr16 \
open-telemetry/opentelemetry-auto-psr14 \
open-telemetry/opentelemetry-auto-http-async \
open-telemetry/opentelemetry-auto-curl \
open-telemetry/opentelemetry-logger-monolog
