FROM alpine:latest

COPY oauth-portal .
# WORKDIR /root
RUN chmod +x ./oauth-portal

CMD [ "./oauth-portal" ]

EXPOSE 8080