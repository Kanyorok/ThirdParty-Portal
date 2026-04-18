# BRERP Third Party Portal Frontend

## Getting Started
Follow these steps to set up the project locally.

### 1. Prerequisites

Make sure you have the following installed:
* [Node.js](https://nodejs.org/) (v21.x or later)
* [Git](https://git-scm.com/)


### 2. Clone the Repository

```bash
git clone [https://github.com/Kimxons/brerp-supplier-portal-frontend.git](https://github.com/Kimxons/brerp-supplier-portal-frontend.git)
cd brerp-supplier-portal-frontend
```
### Install dependencies 
Next, install the necessary project dependencies. Choose the package manager you prefer.

```bash
npm install
# or
yarn install
# or
pnpm install
# or
bun install
```

### Set Up Environment Variables
```bash
cp .env.local.example .env.local
```
Then add your env variables in the .env.local file you just created. 

Recommended local values:

```bash
NEXTAUTH_URL=http://localhost:3000
NEXTAUTH_SECRET=<generate-a-long-random-secret>
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000
NEXT_PUBLIC_EXTERNAL_API_URL=http://127.0.0.1:8000
ERP_BASE_URL=http://127.0.0.1:8000
```

Notes:
* Keep `NEXTAUTH_URL` aligned with the host you actually use in the browser. Prefer `http://localhost:3000` for local work to avoid mixed-host auth/session issues.
* `npm run dev` now validates `.env.local` before starting the dev server.
* Use `npm run verify` to run both linting and type checking before pushing changes.

## Run the Development Server
```bash
npm run dev
or
yarn dev
or
pnpm dev
or 
bun dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser to see the result.


## Tech Stack

* Framework: [Next.js](https://nextjs.org/) (with App Router)
* Language: [TypeScript](https://www.typescriptlang.org/)
* Styling: [Tailwind CSS](https://tailwindcss.com/)
* Theming: [next-themes](https://github.com/pacocoursey/next-themes)
* Code Quality: [ESLint](https://eslint.org/) & [Prettier](https://prettier.io/)

---

## 📜 Available Scripts

The following scripts are available in the project. You can run them using your preferred package manager (`npm`, `yarn`, `pnpm`, or `bun`):

| Script       | Description                                            |
|--------------|--------------------------------------------------------|
| `dev`        | Starts the application in development mode.            |
| `dev:check`  | Validates the required local environment variables.    |
| `build`      | Builds the app for production with optimizations.      |
| `start`      | Starts the production server (requires `build`).       |
| `lint`       | Runs ESLint to check and fix code quality issues.      |
| `typecheck`  | Runs the TypeScript compiler without emitting files.   |
| `verify`     | Runs lint and typecheck together.                      |

---

## Deployment


---

## 🧑Developers

* **Meshack Kitonga** - [GitHub Profile](https://github.com/kimxons)

