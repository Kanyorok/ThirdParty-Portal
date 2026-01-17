import js from "@eslint/js"
import globals from "globals"
import tsParser from "@typescript-eslint/parser"
import tsPlugin from "@typescript-eslint/eslint-plugin"
import reactHooksPlugin from "eslint-plugin-react-hooks"
import nextPlugin from "@next/eslint-plugin-next"

export default [
  {
    ignores: [
      "node_modules/**",
      ".next/**",
      "out/**",
      "build/**",
      "coverage/**",
    ],
  },
  js.configs.recommended,
  {
    files: ["**/*.{js,jsx,ts,tsx}"],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        ecmaVersion: "latest",
        sourceType: "module",
        ecmaFeatures: { jsx: true },
      },
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
    settings: {
      react: { version: "detect" },
    },
    plugins: {
      "@typescript-eslint": tsPlugin,
      "react-hooks": reactHooksPlugin,
      "@next/next": nextPlugin,
    },
    rules: {
      ...reactHooksPlugin.configs.recommended.rules,
      ...nextPlugin.configs.recommended.rules,
      ...nextPlugin.configs["core-web-vitals"].rules,
      ...tsPlugin.configs.recommended.rules,
      "no-undef": "off",
      "no-empty": ["error", { allowEmptyCatch: true }],
      "no-unused-vars": "off",
      "@typescript-eslint/no-unused-vars": [
        "warn",
        { argsIgnorePattern: "^_", varsIgnorePattern: "^_" },
      ],
      "@typescript-eslint/no-explicit-any": "off",
      "@typescript-eslint/no-empty-object-type": "off",
      "react-hooks/set-state-in-effect": "off",
      "react-hooks/immutability": "off",
      "react-hooks/preserve-manual-memoization": "off",
      "react-hooks/static-components": "off",
      "react-hooks/purity": "off",
    },
  },
  {
    files: ["**/*.{ts,tsx}"],
    rules: {
      "no-restricted-syntax": [
        "warn",
        {
          selector:
            "CallExpression[callee.object.name='Math'][callee.property.name='random']",
          message:
            'Avoid Math.random in shared/server code; wrap component with "use client" or use a deterministic value.',
        },
      ],
    },
  },
]
