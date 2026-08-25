import { describe, expect, it } from "vitest"

import { mapApiRound, normalizeCategoryStatus } from "@/lib/rounds"

describe("normalizeCategoryStatus", () => {
    it.each([
        ["P", "APPROVED"],
        ["PREQUALIFIED", "APPROVED"],
        ["A", "APPROVED"],
        ["V", "UNDER_REVIEW"],
        ["REVIEWED", "UNDER_REVIEW"],
        ["U", "UNDER_REVIEW"],
        ["R", "REJECTED"],
        ["F", "REJECTED"],
        ["NOT_PREQUALIFIED", "REJECTED"],
        ["S", "SUBMITTED"],
        [null, "NOT_APPLIED"],
    ])("maps ERP status %s to %s", (status, expected) => {
        expect(normalizeCategoryStatus(status)).toBe(expected)
    })
})

describe("mapApiRound eligibility", () => {
    it("normalizes failed categories and keeps the evaluator reason", () => {
        const round = mapApiRound({
            id: 21,
            title: "Evaluation round",
            categories: [{
                id: 8,
                name: "Audit services",
                hasApplied: true,
                status: "F",
                resultRemarks: "The mandatory licence had expired.",
            }],
        })

        expect(round.appliedCategories?.[0]).toMatchObject({
            status: "REJECTED",
            rejection_reason: "The mandatory licence had expired.",
        })
    })

    it("preserves a pending cross-round application as a non-applicable category", () => {
        const round = mapApiRound({
            id: 20,
            title: "New round",
            categories: [{
                id: 7,
                name: "Office supplies",
                hasApplied: false,
                canApply: false,
                eligibilityStatus: "PENDING_APPLICATION",
                eligibilityMessage: "You already have a pending application for Office supplies.",
                blockingRoundId: 19,
                blockingRoundTitle: "Previous round",
            }],
        })

        expect(round.categories?.[0]).toMatchObject({
            can_apply: false,
            eligibility_status: "PENDING_APPLICATION",
            blocking_round_id: 19,
            blocking_round_title: "Previous round",
        })
        expect(round.availableCategories).toHaveLength(0)
    })
})
