import{_ as s,o as a,c as n,b as l}from"./app.df30a6aa.js";const A=JSON.parse('{"title":"Building Schedules","description":"","frontmatter":{},"headers":[],"relativePath":"guide/scheduling.md","lastUpdated":1658785494000}'),o={name:"guide/scheduling.md"},p=l(`<h1 id="building-schedules" tabindex="-1">Building Schedules <a class="header-anchor" href="#building-schedules" aria-hidden="true">#</a></h1><p><code>sla-timer</code> gives us a pretty flexible Schedule builder to start building your SLA Schedules with.</p><p>By default, if you don&#39;t specify what days you want your SLA to be run on, the schedule defaults to <strong>Every Day</strong>.</p><div class="language-php"><span class="copy"></span><div class="highlight-lines"><br><div class="highlighted">\xA0</div><div class="highlighted">\xA0</div><div class="highlighted">\xA0</div><div class="highlighted">\xA0</div><br><br></div><pre><code><span class="line"><span style="color:#89DDFF;">$</span><span style="color:#A6ACCD;">sla </span><span style="color:#89DDFF;">=</span><span style="color:#A6ACCD;"> </span><span style="color:#FFCB6B;">SLA</span><span style="color:#89DDFF;">::</span><span style="color:#82AAFF;">fromSchedule</span><span style="color:#89DDFF;">(</span></span>
<span class="line"><span style="color:#A6ACCD;">    </span><span style="color:#FFCB6B;">SLASchedule</span><span style="color:#89DDFF;">::</span><span style="color:#82AAFF;">create</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">09:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">17:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">onWeekdays</span><span style="color:#89DDFF;">()-&gt;</span><span style="color:#82AAFF;">and</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">10:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">16:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">onWeekends</span><span style="color:#89DDFF;">()-&gt;</span><span style="color:#82AAFF;">and</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#A6ACCD;">        </span><span style="color:#89DDFF;">-&gt;</span><span style="color:#82AAFF;">from</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">23:00:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">to</span><span style="color:#89DDFF;">(</span><span style="color:#89DDFF;">&#39;</span><span style="color:#C3E88D;">23:30:00</span><span style="color:#89DDFF;">&#39;</span><span style="color:#89DDFF;">)-&gt;</span><span style="color:#82AAFF;">everyDay</span><span style="color:#89DDFF;">()</span></span>
<span class="line"><span style="color:#89DDFF;">);</span></span>
<span class="line"></span></code></pre></div>`,4),e=[p];function t(c,r,F,D,y,i){return a(),n("div",null,e)}var h=s(o,[["render",t]]);export{A as __pageData,h as default};