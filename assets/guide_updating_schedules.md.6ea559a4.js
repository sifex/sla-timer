import{_ as s,o as a,c as t,b as l}from"./app.e566c8d3.js";const u=JSON.parse('{"title":"Updating Schedules","description":"","frontmatter":{},"headers":[],"relativePath":"guide/updating_schedules.md","lastUpdated":1658835961000}'),e={name:"guide/updating_schedules.md"},n=l(`<h1 id="updating-schedules" tabindex="-1">Updating Schedules <a class="header-anchor" href="#updating-schedules" aria-hidden="true">#</a></h1><p>Because sometimes we might need to update our SLA Schedules over time \u2013 without invalidating existing SLA calculations, <code>sla-timer</code> allows us to update the <code>SLA</code> with a new <code>SLASchedule</code> that will commence after the <code>effectiveFrom</code> date provided.</p><div class="language-php"><span class="copy"></span><div class="highlight-lines"><br><br><br><br><br><br><br><br><div class="highlighted">\xA0</div><div class="highlighted">\xA0</div><div class="highlighted">\xA0</div><br><br></div><pre><code><span class="line"><span style="color:#676E95;font-style:italic;">// Create our initial schedule</span></span>
<span class="line"><span style="color:#89DDFF;">$</span><span style="color:#A6ACCD;">sla </span><span style="color:#89DDFF;">=</span><span style="color:#A6ACCD;"> </span><span style="color:#FFCB6B;">SLA</span><span style="color:#89DDFF;">::</span><span style="color:#82AAFF;">fromSchedule</span><span style="color:#89DDFF;">(</span></span>
<span class="line"><span style="color:#A6ACCD;">    </span><span style="color:#FFCB6B;">SLASchedule</span><span style="color:#89DDFF;">::</span><span style="color:#82AAFF;">create</span><span style="color:#89DDFF;">()-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:01:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">everyDay</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#89DDFF;">);</span></span>
<span class="line"></span>
<span class="line"><span style="color:#676E95;font-style:italic;">// Add our new superseded schedule, only starting after 27 July 2022</span></span>
<span class="line"><span style="color:#89DDFF;">$</span><span style="color:#A6ACCD;">sla</span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">addSchedule</span><span style="color:#89DDFF;">(</span></span>
<span class="line"><span style="color:#A6ACCD;">    </span><span style="color:#FFCB6B;">SLASchedule</span><span style="color:#89DDFF;">::</span><span style="color:#82AAFF;">create</span><span style="color:#89DDFF;">()-&gt;</span><span style="color:#82AAFF;">effectiveFrom</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">27-07-2022</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:30</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">onWeekdays</span><span style="color:#89DDFF;">()-&gt;</span><span style="color:#82AAFF;">and</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:10</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">onWeekends</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#89DDFF;">);</span></span>
<span class="line"></span></code></pre></div><p>Then if we run our duration method across both our old schedule and our new schedule.</p><div class="language-php"><span class="copy"></span><pre><code><span class="line"><span style="color:#676E95;font-style:italic;">// Given the time now is 14:00:00 31-07-2022</span></span>
<span class="line"><span style="color:#89DDFF;">$</span><span style="color:#A6ACCD;">sla</span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">duration</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">08:35:40 25-07-2022</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#A6ACCD;">totalSeconds</span><span style="color:#89DDFF;">;</span><span style="color:#A6ACCD;"> </span><span style="color:#676E95;font-style:italic;">// 230</span></span>
<span class="line"></span></code></pre></div><table class="w-full"><thead><tr><th>Tables</th><th class="text-right">Old Schedule</th><th class="text-right">New Schedule</th></tr></thead><tbody><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">25<sup>th</sup> July</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">60s</td><td class="text-right text-neutral-400 dark:text-neutral-500">30s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">26<sup>th</sup> July</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">60s</td><td class="text-right text-neutral-400 dark:text-neutral-500">30s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">27<sup>th</sup> July</td><td class="text-right text-neutral-400 dark:text-neutral-500">60s</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">28<sup>th</sup> July</td><td class="text-right text-neutral-400 dark:text-neutral-500">60s</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">29<sup>th</sup> July</td><td class="text-right text-neutral-400 dark:text-neutral-500">60s</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">30<sup>th</sup> July</td><td class="text-right text-neutral-400 dark:text-neutral-500">60s</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">10s</td></tr><tr><td class="font-bold dark:bg-neutral-900 bg-neutral-100">31<sup>st</sup> July</td><td class="text-right text-neutral-400 dark:text-neutral-500">60s</td><td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">10s</td></tr><tr><td rowspan="2" class="font-bold dark:bg-neutral-900 bg-neutral-200">Total</td><td class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">120s</td><td class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">110s</td></tr><tr><td colspan="2" class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">230s</td></tr></tbody></table>`,6),o=[n];function p(r,c,d,F,D,i){return a(),t("div",null,o)}var g=s(e,[["render",p]]);export{u as __pageData,g as default};