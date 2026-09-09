# Deploying VaultLens publicly

## Why this is simple

This app was scaffolded by xAI's Grok App Builder, which bundles in auth
(`better-auth`) and database (Postgres/PGLite) infrastructure by default. But
after tracing the actual code, **none of it is used by this app**:

- The dashboard (`src/components/vault-dashboard.tsx` and friends) reads only
  local data — `src/data/events.json` and the `.xlsx` files in `public/`.
- No component calls `useCurrentUser`, `authClient`, or any sign-in flow.
- No route mounts `/api/auth/*`, so the whole `better-auth` setup is dead
  code — it's in the bundle but nothing ever calls it.
- No server function touches `@/lib/db`.
- The Vite config (`vite.config.ts`) already targets Nitro's `vercel` build
  preset.

So this deploys as a **static/SSR app with no backend service, no database,
and no required environment variables.**

## Steps (Vercel — recommended, free tier works)

1. **Push this code to a GitHub repo** (or GitLab/Bitbucket).
   ```bash
   cd vaultlens
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin <your-repo-url>
   git push -u origin main
   ```
2. **Go to vercel.com → Add New Project → import that repo.**
3. When Vercel asks for framework settings:
   - Framework preset: **Other**
   - Build command: `npm run build`
   - Output directory: leave as detected (Nitro writes Vercel's Build Output
     API format to `.vercel/output` automatically — you don't need to set
     this manually)
   - Install command: `npm install`
4. Environment variables: **none required.** Optionally add
   `VITE_AUTH_ENABLED=false` (see `.env.example`) to explicitly silence the
   unused auth scaffold — purely cosmetic, doesn't change behavior since
   nothing calls it either way.
5. Click **Deploy**. Vercel gives you a public `*.vercel.app` URL
   immediately; attach a custom domain afterward under
   Project → Settings → Domains if you want one.

## Alternative hosts

Any Node host that runs `npm install && npm run build` and serves the
result works, but you'd lose Nitro's zero-config Vercel output and need to
pick a different Nitro preset (e.g. `node-server`) in `vite.config.ts` and
run `node .output/server/index.mjs` yourself (Render, Railway, Fly.io, a VPS,
etc. all support this pattern). Vercel is the path of least resistance
because the preset is already set for it.

## If you ever DO want accounts or a database later

The scaffold is already there (`src/lib/auth/`, `src/lib/db.ts`,
`migrations/`). Turning it on means:
1. Provisioning your own Postgres (e.g. Neon, Supabase) and setting
   `DATABASE_URL`.
2. Setting `BETTER_AUTH_URL`, `BETTER_AUTH_SECRET`, and your own OAuth app
   credentials — the current code federates sign-in through xAI's Grok auth
   broker (`GROK_AUTH_ISSUER`), which won't work outside their platform, so
   you'd swap `genericOAuth` in `src/lib/auth/server.ts` for direct
   `better-auth` social providers (Google, GitHub, etc.) using your own
   OAuth app credentials instead.
This is real engineering work, not config — happy to help with it if/when
you actually need accounts.
