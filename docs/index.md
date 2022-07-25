---
layout: home

hero:
  name: SLA Timer
  text: Your PHP Package for SLA tracking
  tagline: A PHP package for calculating & tracking the Service Level Agreement completion timings.
  actions:
    - theme: brand
      text: Get Started
      link: /guide/getting_started
    - theme: alt
      text: View on GitHub
      link: https://github.com/sifex/sla-timer
features:
  - title: Easy Scheduling
    details: SLA Timer makes it super easy to build your own custom SLA schedules 
  - title: Breaches
    details: Set multiple thresholds for when your SLAs are breached
  - title: Holiday Time
    details: Simply set up skipped days, holidays, & setup pause periods for your SLAs
---


<script>
import {ref} from "vue"; 

let want_job = ref(false)
</script>
<div class="px-6 sm:px-12 lg:px-16">
  <div class="container" style="max-width: 1152px; margin: 0 auto; text-align: center; padding: 40px 0;">
      <a href="https://twitter.com/sifex/status/1548374115815346178">
          <img @load="want_job = true" src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/hiring.svg?" alt="Hi, I'm Alex & I'm currently looking for a Laravel job. Please reach out to me via twitter, or click this link." height="49">
      </a>
      <div v-if="!want_job" class="rounded-xl bg-slate-200 animate-pulse flex space-x-4" style="max-width: 895px; aspect-ratio: 895 / 49;"></div>
  </div>
</div>

