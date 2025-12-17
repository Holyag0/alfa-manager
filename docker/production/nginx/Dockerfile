FROM nginx:1.25-alpine

LABEL maintainer="Holyago"

# Copiar configurações do nginx
COPY docker/production/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/nginx/default.conf /etc/nginx/conf.d/default.conf

# Criar diretórios necessários
RUN mkdir -p /var/www/html/public \
    && mkdir -p /var/log/nginx \
    && chown -R nginx:nginx /var/www/html \
    && chown -R nginx:nginx /var/log/nginx

# Instalar wget para healthcheck
RUN apk add --no-cache wget

WORKDIR /var/www/html/public

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]