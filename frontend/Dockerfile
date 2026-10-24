# STAGE 1: Build aplikasi Vue 3 dengan Node.js 18
FROM node:18-alpine AS build-stage

WORKDIR /app

# Copy package.json & package-lock.json untuk optimasi cache
COPY package*.json ./

# Install dependensi
RUN npm ci

# Copy seluruh source code frontend
COPY . .

# Kompilasi aplikasi untuk produksi
RUN npm run build


# STAGE 2: Nginx Web Server (Hasil build tanpa node_modules)
FROM nginx:alpine AS production-stage
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Salin konfigurasi Nginx custom agar Nginx paham rute /api
COPY default.conf /etc/nginx/conf.d/default.conf

# Copy hasil kompilasi dari Stage 1
COPY --from=build-stage /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]