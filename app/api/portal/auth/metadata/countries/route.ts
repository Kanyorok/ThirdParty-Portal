import { createMetadataGetHandler } from "@/app/api/portal/auth/metadata/_shared"
import { normalizeRegistrationCountries } from "@/lib/registration-countries"

export const GET = createMetadataGetHandler(
    [
        "/portal/auth/metadata/countries",
        "/api/v1/portal/auth/metadata/countries",
    ],
    normalizeRegistrationCountries,
)