FROM golang:1.24.3-alpine

RUN apk add --no-cache mysql-client git

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .

ENTRYPOINT ["/app/entrypoint.sh"]