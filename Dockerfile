
# --- Builder Stage ---
FROM node:20-bookworm-slim AS builder

# Declare all build-time variables
ARG NEXT_PUBLIC_EXTERNAL_API_URL
ARG EXTERNAL_API_URL
ARG NEXTAUTH_URL
ARG API_BASE_URL
ARG SANCTUM_STATEFUL_DOMAINS

# Set build-time environment variables
ENV TAILWIND_DISABLE_OXIDE=1 \
    NEXT_TELEMETRY_DISABLED=1 \
    NEXT_PUBLIC_EXTERNAL_API_URL=${NEXT_PUBLIC_EXTERNAL_API_URL} \
    EXTERNAL_API_URL=${EXTERNAL_API_URL} \
    NEXTAUTH_URL=${NEXTAUTH_URL} \
    API_BASE_URL=${API_BASE_URL} \
    SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}

WORKDIR /app

COPY package.json ./
RUN npm install --legacy-peer-deps

COPY . .

# Build with all relevant env vars
RUN npm run build

# --- Production Stage ---
FROM node:20-bookworm-slim AS runner

WORKDIR /app

# Set runtime environment variables (only those needed at runtime)
ENV NODE_ENV=production \
    PORT=3000 \
    NEXT_TELEMETRY_DISABLED=1

# Create non-root user
RUN groupadd -g 1001 nodejs && \
    useradd -m -u 1001 -g nodejs nextjs

# Copy built application
COPY --from=builder --chown=nextjs:nodejs /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs
EXPOSE 3000
CMD ["node", "server.js"]
