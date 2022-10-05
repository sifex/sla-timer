# About SLAs

### ☑️  What is an SLA?

> A **service-level agreement** _(shortened to SLA)_ is a commitment between a service provider and a client. Particular aspects of the service – quality, availability, responsibilities – are agreed between the service provider and the service user.

### ⏳ What is an SLA Timer?

An **SLA timer** is used to keep on top of things like "time-to-first-action", "time-to-resolution", and to gauge whether your commitments to customer responsiveness are being met. 

Examples of some time-based SLAs you can measure are:

1. Respond to all requests within 2 days.
2. Resolve all high-priority requests within 18 hours.

## 👾 How are SLAs calculated?

First our SLA requires a schedule to operate over. We might want to start counting up whenever a support ticket should be being actions or addressed. This will usually look similar to below if we're dealing with the usual 9-5.  

<a :href="withBase('/images/sla_gaps_light.svg')" class="lg:-mx-16 my-16 lg:my-24 xl:my-32 block">
    <img :src="withBase('/images/sla_gaps_dark.svg')" alt="SLA Gaps" class="w-full hidden dark:block">
    <img :src="withBase('/images/sla_gaps_light.svg')" alt="SLA Gaps" class="w-full dark:hidden">
</a>

**SLA Timers** operate by calculating the period over which our **"Subject"** has crossed over our defined SLA schedule.

---

The following shows an SLA for "Business Hours" (Ie. 9-5pm Monday through to Friday).

<script setup>
import { withBase } from 'vitepress';
</script>


<a :href="withBase('/images/sla_basic_light.svg')" class="lg:-mx-16 my-16 lg:my-24 xl:my-32 block">
    <img :src="withBase('/images/sla_basic_dark.svg')" alt="SLA Basics" class="w-full hidden dark:block">
    <img :src="withBase('/images/sla_basic_light.svg')" alt="SLA Basics" class="w-full dark:hidden">
</a>

Our **"Elapsed"** time is currently at `36 hours`. If we define two target periods within our SLA, we can calculate whether any of our targets have been met – or if they haven't – whether any targets have been **"Breached"**.

## How do I use `sifex/sla-timer`?

🎉 Head on over to **[Getting Started ›](/guide/getting_started.md)** to see how you can use this library