import { permanentRedirect } from "next/navigation"

export default function FeedbackRedirectPage() {
  permanentRedirect("/dashboard/help/tickets")
}
