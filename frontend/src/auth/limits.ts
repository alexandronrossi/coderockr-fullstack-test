/** Keep in sync with App\Support\Auth\AuthFieldLimits */
export const AUTH_FIELD_LIMITS = {
  name: 255,
  email: 255,
  password: 72,
  passwordMin: 8,
} as const;
