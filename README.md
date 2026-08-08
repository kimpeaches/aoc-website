# Assist on Call — Website Rebuild

A complete, static, dependency-free rebuild of assistoncall.com with a modern editorial design,
scroll animations and cover videos, plus a dedicated page for each of the three companies.

**Palette:** Navy blue `#0B2545` · Teal `#12A8A0` · Light blue `#8ECAE6` · White `#FFFFFF`
**Typeface:** DM Sans (loaded from Google Fonts)
**Design reference:** editorial/animation language inspired by biofarma.com.ar (no content reused)

---

## Folder structure

```
assist-on-call-website/
├── index.html                    Landing page — 3 entity cards, cover video, marquee, scroll statement
├── home-health.html              DEPARTMENT 01 — all 7 services in full
├── hospice.html                  DEPARTMENT 02 — all 6 care programmes, core values, team roster
├── private-duty.html             DEPARTMENT 03 — free assessment, 10 care tasks, caregiver credentials
├── about.html
├── contact.html
├── set-an-appointment.html
├── send-your-referrals.html
├── view-map-and-directions.html
├── testimonials.html
├── resources.html
├── blog.html
├── blog-home-health-aides-improve-daily-life.html
├── blog-enhancing-communication-through-speech-therapy.html
├── blog-top-benefits-of-skilled-nursing-at-home.html
├── careers-clinicians.html
├── careers-caregivers.html
├── sitemap.html
├── privacy-notice.html
├── do-not-sell.html
├── robots.txt
└── assets/
    ├── css/style.css             Full design system (~24 KB, no framework)
    ├── js/main.js                Animations, accordion, video controls, nav
    ├── img/                      ← download the photos listed below into here
    └── video/                    ← download the cover videos listed below into here
```

## How to preview

Open `index.html` in any browser. Everything is static HTML/CSS/JS — no build step, no npm, no framework.

---

## IMPORTANT: download the media before going live

Every image and video currently points at Pexels CDN URLs so the site previews immediately.
**Do not ship it that way.** Hotlinking is slow, can break without warning, and leaks visitor
requests to a third party. Download each asset into `assets/video/` and `assets/img/`, then
find-and-replace the CDN URLs with local paths.

All assets below are **Pexels License** — free for commercial use, no attribution required
(attribution is still appreciated). Verify each licence on its page before publishing.

### Videos → `assets/video/`

Download page: `https://www.pexels.com/video/<ID>/` · Direct HD file pattern:
`https://videos.pexels.com/video-files/<ID>/<ID>-hd_1920_1080_25fps.mp4`

| Pexels ID | Use | Appears on |
|---|---|---|
| 7475237 | Home Health — hero cover video (nurse on a home visit) | home-health.html, index.html |
| 7475239 | Home Health — intro split video | home-health.html |
| 7522364 | Hospice — hero cover video (caregiver comforting a patient) | hospice.html |
| 8057825 | Hospice — intro split video | hospice.html |
| 7522353 | Hospice — spare/alternate clip | (unused spare) |
| 7517374 | Private Duty — hero cover video (caregiver in conversation at home) | private-duty.html |
| 7517387 | Private Duty — free assessment split video | private-duty.html |
| 7517688 | Private Duty — spare/alternate clip | (unused spare) |
| 6646677 | Private Duty — spare/alternate clip | (unused spare) |

### Photos → `assets/img/`

Download page: `https://www.pexels.com/photo/<ID>/` · Direct file pattern:
`https://images.pexels.com/photos/<ID>/pexels-photo-<ID>.jpeg?auto=compress&cs=tinysrgb&w=1600`

| Pexels ID | Subject | Appears on |
|---|---|---|
| 7345476 | Skilled nursing / home health nurse | home-health.html, index.html, blog post 3 |
| 7551594 | Home health aide assisting a client | home-health.html, index.html, testimonials.html, blog post 1 |
| 7551627 | Physical therapy / nurse in living room | home-health.html, index.html, about.html, blog.html, set-an-appointment.html |
| 5473223 | Occupational therapy — hand work | home-health.html |
| 7176321 | Speech therapy session | home-health.html, blog post 2 |
| 7345460 | Medical social worker with a family | home-health.html, about.html, contact.html, resources.html, do-not-sell.html |
| 8088898 | Durable medical equipment at home | home-health.html, hospice.html |
| 11510370 | Assisted living / placement referrals | home-health.html, view-map-and-directions.html, sitemap.html |
| 6753272 | Hospice — holding a patient’s hand | index.html, hospice.html |
| 8899458 | Hospice medical care at the bedside | hospice.html |
| 8972606 | Hospice personal care / grooming | hospice.html |
| 5875116 | Spiritual support — candle and folded hands | hospice.html |
| 6647051 | Emotional and bereavement support | hospice.html |
| 7446782 | Trained volunteer visiting a patient | hospice.html |
| 7551614 | Private duty caregiver with a client | private-duty.html, careers-caregivers.html |
| 8376202 | Private duty — free assessment poster frame | private-duty.html |
| 8581029 | Private duty — spare | (unused spare) |
| 8065095 | Clinical team / records | careers-clinicians.html, send-your-referrals.html, privacy-notice.html |
| 6129494 | Caregiver preparing a meal | careers-caregivers.html |

### Recommended encoding for the cover videos

```bash
# 1080p H.264 web version
ffmpeg -i source.mp4 -vf scale=1920:-2 -c:v libx264 -crf 24 -preset slow -an \
  -movflags +faststart assets/video/home-health-hero.mp4

# WebM fallback (smaller on modern browsers)
ffmpeg -i source.mp4 -vf scale=1920:-2 -c:v libvpx-vp9 -crf 34 -b:v 0 -an \
  assets/video/home-health-hero.webm

# Poster frame
ffmpeg -i source.mp4 -ss 00:00:03 -frames:v 1 assets/img/home-health-hero.jpg
```

Keep each hero clip under roughly 6 MB and 12–20 seconds. All hero videos are already
`muted` + `loop` + `playsinline` + `autoplay`, are paused automatically when scrolled
off-screen, and have a visible play/pause control for accessibility.

---

## Content issues found on the original site — please decide on each

1. **Address inconsistency.** The home health suite number appears as **Ste. 8-A** in the original
   footer but **Ste. 8b** in the header. This build uses Ste. 8-A for home health and Ste. 8b for
   hospice throughout. Confirm which is correct for each company and correct it everywhere
   (`home-health.html`, `hospice.html`, `contact.html`, `view-map-and-directions.html`, footer in every page).

2. **Social Security number on the caregiver application.** The original public application form
   collected an SSN. That field has been deliberately **removed** here, with a note explaining that
   sensitive identifiers should be collected through a secure onboarding process after an offer.
   Collecting SSNs over an unencrypted public web form is a serious liability.

3. **Broken Medicare link.** The original Resources page listed `www.medicare.go`. Corrected to
   `https://www.medicare.gov` in `resources.html`, with a visible note you can delete once verified.

4. **Unnamed accreditation seal.** The original site shows a generic gold coin graphic
   (`coin-icon.png`) under the words "Accredited by:" with no accrediting body named. The footer of
   every page here has a placeholder — replace it with your actual accreditor’s official logo
   (ACHC, CHAP, Joint Commission, etc.), or remove the seal entirely. An unattributed
   accreditation claim is a compliance risk.

5. **Testimonials.** Only one client testimonial was recoverable from the original site. Paste any
   remaining testimonials into `testimonials.html` (one `<figure class="quote">` block each).

6. **Forms are not wired up.** Every form uses `action="#"`. Point them at your form handler or
   mail service. Anything that will carry patient information (the referral form especially) must
   post over HTTPS to a **HIPAA-compliant** endpoint with a signed business associate agreement.

7. **Legal pages need review.** `privacy-notice.html` and `do-not-sell.html` reproduce the structure
   and substance of the originals but must be reviewed by your compliance officer or attorney, and
   an effective date inserted, before publishing.

---

## Accessibility & performance notes

- `prefers-reduced-motion` is fully respected: reveals, marquee and autoplaying video all stand down.
- The service accordions are real `<button>` elements with `aria-expanded` / `aria-controls`,
  keyboard arrow navigation, and deep-linking (`home-health.html#skilled-nursing` opens that service).
- Hero videos are decorative, muted, and have a manual pause control.
- Off-screen videos are paused automatically to save bandwidth and battery.
- Add real `alt` text review before launch; placeholders are descriptive but should be checked.
- Compress all photos (WebP/AVIF at 1600px max) and add `width`/`height` attributes to stop layout shift.

## Before launch checklist

- [ ] Download all media locally and replace the Pexels URLs
- [ ] Replace the accreditation placeholder in the footer
- [ ] Confirm the Ste. 8-A / 8b addresses
- [ ] Wire up all forms to a HIPAA-compliant handler
- [ ] Legal review of the two privacy pages, and insert effective dates
- [ ] Add remaining testimonials
- [ ] Add favicon and Open Graph / Twitter card images
- [ ] Generate an XML sitemap and submit it to Search Console
- [ ] Set up 301 redirects from every old URL to its new equivalent
- [ ] Test on iOS Safari specifically (autoplay + `playsinline` behaviour)
