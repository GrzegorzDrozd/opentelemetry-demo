curl -X PUT "http://localhost:9200/_index_template/ss4o_traces" \
  -H "Content-Type: application/json" \
  -d'
{
  "index_patterns": ["otel-v1-apm-span-*"],
  "template": {
    "settings": {
      "number_of_shards": 1,
      "number_of_replicas": 1
    },
    "mappings": {
      "properties": {
        "@timestamp": { "type": "date" },
        "trace.id":   { "type": "keyword" },
        "span.id":    { "type": "keyword" },
        "parent.id":  { "type": "keyword" },
        "name":       { "type": "text" },
        "duration":   { "type": "long" },
        "service.name": { "type": "keyword" },
        "attributes": { "type": "object" }
      }
    }
  },
  "priority": 500
}
'

curl -X PUT "http://localhost:9200/_index_template/ss4o_metrics" \
  -H "Content-Type: application/json" \
  -d'
{
  "index_patterns": ["ss4o_metrics*"],
  "template": {
    "settings": {
      "number_of_shards": 1,
      "number_of_replicas": 1
    },
    "mappings": {
      "properties": {
        "@timestamp":   { "type": "date" },
        "metric.name":  { "type": "keyword" },
        "metric.value": { "type": "float" },
        "service.name": { "type": "keyword" },
        "attributes":   { "type": "object" }
      }
    }
  },
  "priority": 500
}
'

curl -X PUT "http://localhost:9200/_index_template/ss4o_metrics" \
  -H "Content-Type: application/json" \
  -d'
{
  "index_patterns": ["ss4o_metrics*"],
  "template": {
    "settings": {
      "number_of_shards": 1,
      "number_of_replicas": 1
    },
    "mappings": {
      "properties": {
        "@timestamp":   { "type": "date" },
        "metric.name":  { "type": "keyword" },
        "metric.value": { "type": "float" },
        "service.name": { "type": "keyword" },
        "attributes":   { "type": "object" }
      }
    }
  },
  "priority": 500
}
'
