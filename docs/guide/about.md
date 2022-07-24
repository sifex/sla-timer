# About SLAs

## ☑️ What is an SLA?

> A **service-level agreement** _(shortened to SLA)_ is a commitment between a service provider and a client. Particular aspects of the service – quality, availability, responsibilities – are agreed between the service provider and the service user.

## ⏳ What is an SLA Timer?

An **SLA timer** is used to keep on top of things like "time-to-first-action", "time-to-resolution", and to gauge whether your commitments to customer responsiveness are being met. 

Examples of some time-based SLAs you can measure are:

1. Respond to all requests within 2 hours.
2. Resolve high-priority requests within 24 hours.

## 👾 How are SLAs calculated?

The following shows an SLA for "Business Hours" (Ie. 9-5pm Monday through to Friday).

<script setup>
import { withBase } from 'vitepress';
</script>


<a :href="withBase('/images/sla_basic_light.svg')" class="lg:-mx-16 my-16 block">
    <img :src="withBase('/images/sla_basic_dark.svg')" alt="SLA Basics" class="w-full hidden dark:block">
    <img :src="withBase('/images/sla_basic_light.svg')" alt="SLA Basics" class="w-full dark:hidden">
</a>

## How do I use `sla-timer`?

🎉 Head on over to **[Getting Started ›](/guide/getting_started.md)** to see how you can use this library