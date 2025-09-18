# --- Builder Stage ---
FROM node:20-alpine AS builder
ARG EXTERNAL_API_URL
ARG NEXTAUTH_SECRET
ARG NEXTAUTH_URL
ARG NEXT_PUBLIC_EXTERNAL_API_URL
ARG API_BASE_URL
ARG SANCTUM_STATEFUL_DOMAINS

WORKDIR /app

# Copy package files and install dependencies
COPY package.json package-lock.json* ./

# Use npm install instead of npm ci to handle lock file mismatches
RUN npm install --legacy-peer-deps

# Copy source code and build
COPY . .
RUN EXTERNAL_API_URL=${EXTERNAL_API_URL} \
    NEXT_TELEMETRY_DISABLED=1 \
    NEXTAUTH_SECRET=${NEXTAUTH_SECRET} \
    NEXTAUTH_URL=${NEXTAUTH_URL} \
    NEXT_PUBLIC_EXTERNAL_API_URL=${NEXT_PUBLIC_EXTERNAL_API_URL} \
    API_BASE_URL=${API_BASE_URL} \
    SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS} \
    npm run build

# --- Production Stage ---
FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production \
    PORT=3000 \
    NEXT_TELEMETRY_DISABLED=1

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S nextjs -u 1001

# Copy built application
COPY --from=builder --chown=nextjs:nodejs /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

CMD ["node", "server.js"]
