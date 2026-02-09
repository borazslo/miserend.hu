FROM busybox:1.36

COPY elasticsearch_data.tar.gz /elasticsearch_data.tar.gz

ENTRYPOINT ["/bin/sh", "-c", "\
  if [ \"$(ls -A /data 2>/dev/null)\" ]; then \
    echo 'Elasticsearch data already present, skipping.'; \
    exit 0; \
  fi; \
  echo 'Initializing Elasticsearch data...'; \
  tar -xzf /elasticsearch_data.tar.gz -C /data; \
  chown -R 1000:1000 /data; \
  echo 'Done.' \
"]