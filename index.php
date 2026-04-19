<?php
// ============================================================
//  index.php — الموقع الرئيسي يقرأ الأخبار ديناميكياً
// ============================================================
$newsFile = __DIR__ . '/news.json';
$allNews  = file_exists($newsFile) ? (json_decode(file_get_contents($newsFile), true) ?: []) : [];
// فقط المنشورة
$published = array_values(array_filter($allNews, fn($n) => ($n['status'] ?? 'published') === 'published'));
// أحدث 4 للعرض
$newsToShow = array_slice(array_reverse($published), 0, 4);
// دالة مساعدة
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function nl2p(string $s): string {
    $paras = array_filter(explode("\n", $s), fn($l) => trim($l) !== '');
    return implode('', array_map(fn($l) => '<p class="nmo-txt">'.h(trim($l)).'</p>', $paras));
}
?><!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-DNS-Prefetch-Control" content="on"/>
<link rel="dns-prefetch" href="//fonts.googleapis.com"/>
<link rel="dns-prefetch" href="//fonts.gstatic.com"/>
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<meta content="width=device-width,initial-scale=1" name="viewport"/>
<meta content="الثانوية التأهيلية عمرو بن العاص - المديرية الإقليمية لأكادير إداوتنان" name="description"/>
<title>الثانوية التأهيلية عمرو بن العاص</title>
<meta name="theme-color" content="#1C1410"/>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Kufi+Arabic:wght@300;400;600;700&display=swap" media="print" onload="this.media='all'" rel="stylesheet"/>
<noscript><link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Kufi+Arabic:wght@300;400;600;700&display=swap" rel="stylesheet"/></noscript>
<style>
/* ===== كل الـ CSS من الملف الأصلي ===== */
*{margin:0;padding:0;box-sizing:border-box}
:root{--r:#B5202C;--r2:#8B1520;--g:#C9A84C;--g2:#E8C97A;--k:#1C1410;--k2:#2A2420;--cr:#FAF6EE;--pa:#F0E8D4;--gr:#6B5E54}
html{scroll-behavior:smooth}
body{font-family:'Noto Kufi Arabic',sans-serif;background:var(--cr);color:var(--k);overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:#111}::-webkit-scrollbar-thumb{background:var(--g);border-radius:3px}
#ld{display:none!important}
.lr{width:66px;height:66px;border-radius:50%;border:2px solid transparent;border-top-color:var(--g);animation:sp 1s linear infinite;box-shadow:0 0 20px rgba(201,168,76,.3)}
@keyframes sp{to{transform:rotate(360deg)}}
.lt{font-family:'Amiri',serif;color:var(--g);font-size:17px;letter-spacing:2px}
.lb{width:175px;height:1px;background:#333;overflow:hidden}
.lf{display:block;height:100%;width:0;background:var(--g);animation:lfi 1.2s ease forwards}
@keyframes lfi{to{width:100%}}
#annbar{background:linear-gradient(135deg,#C9A84C,#E8C97A);border-right:5px solid #8B1520;border-radius:8px;margin:0 52px 0 52px;padding:18px 24px;display:flex;align-items:flex-start;gap:16px;box-shadow:0 4px 18px rgba(201,168,76,.3)}
.ann-icon{font-size:28px;flex-shrink:0;line-height:1}
.ann-content{flex:1}
.ann-title{font-family:'Amiri',serif;font-size:17px;font-weight:700;color:#1C1410;margin-bottom:6px}
.ann-text{font-size:13px;color:#2A2420;line-height:1.8}
header{position:fixed;top:0;left:0;right:0;z-index:500;transition:background .3s,box-shadow .3s}
header.sc{background:rgba(28,20,16,.97);backdrop-filter:blur(16px);border-bottom:1px solid rgba(201,168,76,.2);box-shadow:0 2px 24px rgba(0,0,0,.4)}
nav{display:flex;align-items:center;justify-content:space-between;padding:16px 52px;max-width:1300px;margin:0 auto}
.logo{display:flex;align-items:center;gap:12px;text-decoration:none}
.lbox{width:44px;height:44px;background:var(--r);border:2px solid var(--g);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 14px rgba(181,32,44,.4)}
.lar{font-family:'Amiri',serif;font-size:14px;color:var(--g);font-weight:700;line-height:1.2;max-width:180px}
.lfr{font-size:10px;color:rgba(255,255,255,.38);letter-spacing:1.5px}
.nu{display:flex;gap:3px;list-style:none}
.nu a{color:rgba(255,255,255,.68);text-decoration:none;font-size:13px;padding:8px 12px;border-radius:4px;transition:.2s}
.nu a:hover{color:var(--g2);background:rgba(201,168,76,.08)}
.nu .ct{background:var(--r);color:#fff!important;border:1px solid rgba(181,32,44,.5);font-weight:600;box-shadow:0 3px 12px rgba(181,32,44,.3)}
.nu .ct:hover{background:var(--r2)}
.bg{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px}
.bg span{width:22px;height:2px;background:var(--g);border-radius:2px}
#mob{display:none;position:fixed;inset:0;z-index:490;background:rgba(28,20,16,.98);flex-direction:column;align-items:center;justify-content:center}
#mob.open{display:flex}
#mob a{font-family:'Amiri',serif;font-size:22px;color:rgba(255,255,255,.75);text-decoration:none;padding:14px 48px;width:100%;text-align:center;border-bottom:1px solid rgba(201,168,76,.1);transition:color .2s}
#mob a:hover{color:var(--g)}
.xb{position:absolute;top:20px;left:22px;font-size:24px;color:var(--g);background:none;border:none;cursor:pointer}
.hero{position:relative;height:100vh;min-height:660px;display:flex;align-items:center;overflow:hidden;background:var(--k)}
.hbg{position:absolute;inset:0;background-size:cover;background-position:center 25%;animation:hz 7s ease forwards;will-change:transform}
@keyframes hz{from{transform:scale(1.06)}to{transform:scale(1)}}
.ho{position:absolute;inset:0;background:linear-gradient(to left,rgba(28,20,16,.12) 0%,rgba(28,20,16,.82) 52%,rgba(28,20,16,.97) 100%)}
.ho2{position:absolute;inset:0;background:linear-gradient(to top,rgba(28,20,16,.9) 0%,transparent 44%)}
.hp{position:absolute;inset:0;opacity:.03;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56'%3E%3Cpath d='M28 4L52 28L28 52L4 28Z' fill='none' stroke='%23C9A84C' stroke-width='1'/%3E%3C/svg%3E");background-size:56px 56px}
.hc{position:relative;z-index:5;padding:0 52px;padding-top:88px;max-width:1300px;margin:0 auto;width:100%}
.ew{display:flex;align-items:center;gap:13px;margin-bottom:22px;opacity:0;animation:fR .8s .4s forwards}
.el{width:32px;height:1px;background:var(--g)}
.et{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--g);font-weight:600}
.hh{font-family:'Amiri',serif;font-size:clamp(38px,7vw,80px);color:#fff;line-height:1.2;margin-bottom:10px;opacity:0;animation:fR .8s .6s forwards}
.hh span{color:var(--g);display:block}
.hfr{font-size:clamp(11px,1.2vw,14px);color:rgba(255,255,255,.38);letter-spacing:3px;text-transform:uppercase;font-weight:300;margin-bottom:34px;opacity:0;animation:fR .8s .8s forwards}
.hst{display:flex;gap:40px;margin-bottom:44px;opacity:0;animation:fR .8s 1s forwards}
.hs{padding-right:16px;position:relative}
.hs+.hs::before{content:'';position:absolute;left:0;top:10%;bottom:10%;width:1px;background:rgba(201,168,76,.22)}
.hsn{font-family:'Amiri',serif;font-size:38px;color:var(--g);line-height:1}
.hsl{font-size:11px;color:rgba(255,255,255,.4);letter-spacing:2px;margin-top:4px}
.hbt{display:flex;gap:12px;flex-wrap:wrap;opacity:0;animation:fR .8s 1.2s forwards}
@keyframes fR{from{opacity:0;transform:translateX(-22px)}to{opacity:1;transform:translateX(0)}}
.scue{position:absolute;bottom:28px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.26);font-size:10px;letter-spacing:3px;display:flex;flex-direction:column;align-items:center;gap:6px;animation:bob 2s ease-in-out infinite}
@keyframes bob{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(7px)}}
.tk{background:var(--r);padding:10px 0;overflow:hidden;border-top:1px solid rgba(255,255,255,.1)}
.ti{display:inline-flex;white-space:nowrap;animation:tick 28s linear infinite}
.tit{font-size:13px;color:rgba(255,255,255,.9);padding:0 32px}
.ts{color:var(--g2)}
@keyframes tick{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.btn{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:4px;font-family:'Noto Kufi Arabic',sans-serif;font-size:14px;font-weight:600;text-decoration:none;cursor:pointer;border:none;transition:all .25s}
.bg2{background:var(--g);color:var(--k)}.bg2:hover{background:var(--g2);transform:translateY(-2px);box-shadow:0 8px 20px rgba(201,168,76,.28)}
.bgh{background:transparent;color:rgba(255,255,255,.78);border:1px solid rgba(255,255,255,.2)}.bgh:hover{border-color:var(--g);color:var(--g);background:rgba(201,168,76,.05)}
section{padding:90px 52px}
.wrap{max-width:1200px;margin:0 auto}
.sl{display:flex;align-items:center;gap:12px;margin-bottom:15px}
.sl-l{width:26px;height:1px;background:var(--g)}
.sl-t{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--g);font-weight:600}
.sh{font-family:'Amiri',serif;font-size:clamp(26px,4vw,46px);line-height:1.25;margin-bottom:12px}
.sh.lt{color:#fff}
.sp{font-size:14px;color:var(--gr);line-height:1.9;max-width:560px}
.sp.lt{color:rgba(255,255,255,.5)}
.dv{width:52px;height:2px;background:linear-gradient(90deg,var(--r),var(--g));border-radius:2px;margin:15px 0}
.rv{opacity:0;transform:translateY(16px);transition:opacity .5s,transform .5s}
.rv.in{opacity:1;transform:translateY(0)}
.d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}.d4{transition-delay:.4s}
.about{background:var(--cr)}
.ag{display:grid;grid-template-columns:1fr 1.1fr;gap:74px;align-items:center}
.aiw{position:relative}
.aim{width:100%;aspect-ratio:4/3;object-fit:cover;object-position:center 20%;display:block;border-radius:2px;box-shadow:16px 16px 0 var(--pa),18px 18px 0 var(--r)}
.abd{position:absolute;bottom:-24px;right:-24px;width:106px;height:106px;border-radius:50%;background:var(--r);border:3px solid var(--g);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 24px rgba(181,32,44,.4);text-align:center}
.abn{font-family:'Amiri',serif;font-size:27px;line-height:1}
.abl{font-size:10px;letter-spacing:1px;opacity:.8;margin-top:3px}
.aq{border-right:3px solid var(--g);padding-right:17px;margin:22px 0;font-family:'Amiri',serif;font-size:19px;font-style:italic;color:var(--k2);line-height:1.6}
.fts{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:24px}
.ft{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #EDE6D8;border-radius:6px;padding:10px 13px;font-size:13px;color:var(--k2);transition:.2s;box-shadow:0 2px 6px rgba(0,0,0,.03)}
.ft:hover{border-color:var(--g);background:var(--pa);transform:translateY(-2px)}
.anns{background:var(--pa)}
.ann-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-top:40px}
.ann-card{background:#fff;border:1px solid #E8DEC8;border-radius:8px;overflow:hidden;transition:all .3s;box-shadow:0 2px 8px rgba(0,0,0,.05)}
.ann-card:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,.1);border-color:var(--g)}
.ann-carousel-wrap{position:relative;margin-top:40px;overflow:hidden}
.ann-carousel-track{display:flex;transition:transform .55s cubic-bezier(.4,0,.2,1)}
.ann-carousel-slide{min-width:100%;box-sizing:border-box}
.ann-carousel-slide .ann-card{max-width:720px;margin:0 auto}
.ann-ctrl{display:flex;align-items:center;justify-content:center;gap:18px;margin-top:22px}
.ann-ctrl-btn{width:40px;height:40px;border-radius:50%;border:1.5px solid rgba(201,168,76,.4);background:#fff;color:var(--g);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.25s;box-shadow:0 2px 8px rgba(0,0,0,.07)}
.ann-ctrl-btn:hover{background:var(--g);color:var(--k);border-color:var(--g);transform:scale(1.08)}
.ann-dots{display:flex;gap:7px;align-items:center}
.ann-dot{width:9px;height:9px;border-radius:50%;border:none;background:rgba(201,168,76,.3);cursor:pointer;padding:0;transition:.3s}
.ann-dot.active{background:var(--g);width:22px;border-radius:4px}
.ann-progress-bar{height:3px;background:rgba(201,168,76,.15);border-radius:3px;margin-top:12px;overflow:hidden}
.ann-progress-fill{height:100%;background:linear-gradient(90deg,var(--r),var(--g));border-radius:3px;width:0;transition:width linear}
.fsoc{display:flex;gap:10px;margin-top:14px}
.fsoc-a{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:15px;color:#fff;transition:.2s;border:1px solid rgba(255,255,255,.1);flex-shrink:0}
.fsoc-a:hover{transform:translateY(-3px);box-shadow:0 6px 18px rgba(0,0,0,.3)}
.fsoc-fb{background:#1877f2}.fsoc-ig{background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)}.fsoc-yt{background:#ff0000}.fsoc-wa{background:#25d366}
.ann-card-top{height:5px}
.ann-card-top.adm{background:linear-gradient(90deg,var(--r),var(--g))}
.ann-card-top.exam{background:linear-gradient(90deg,#1a5e2a,#4caf50)}
.ann-card-body{padding:22px 24px}
.ann-card-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.ann-card-tag{font-size:10px;letter-spacing:2px;text-transform:uppercase;font-weight:700;padding:3px 10px;border-radius:3px}
.ann-card-tag.adm{color:var(--r);background:rgba(181,32,44,.08);border:1px solid rgba(181,32,44,.18)}
.ann-card-tag.exam{color:#1a5e2a;background:rgba(26,94,42,.08);border:1px solid rgba(26,94,42,.2)}
.ann-card-date{font-size:11px;color:#999;display:flex;align-items:center;gap:5px}
.ann-card-title{font-family:'Amiri',serif;font-size:18px;color:var(--k);line-height:1.4;margin-bottom:10px}
.ann-card-text{font-size:13px;color:var(--gr);line-height:1.85}
.nbs{background:var(--k);position:relative;overflow:hidden}
.nbs::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 0%,rgba(181,32,44,.11),transparent 62%);pointer-events:none}
.nctr{text-align:center;margin-bottom:48px}
.nctr .sl{justify-content:center}
.ng{display:grid;grid-template-columns:repeat(2,1fr);gap:1px;background:rgba(201,168,76,.11);border:1px solid rgba(201,168,76,.11);border-radius:4px;overflow:hidden;max-width:700px;margin:0 auto}
.nc{background:rgba(28,20,16,.85);padding:44px 24px;text-align:center;position:relative;transition:background .3s}
.nc::after{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--g),transparent);opacity:0;transition:.3s}
.nc:hover{background:rgba(28,20,16,.5)}.nc:hover::after{opacity:1}
.ni{font-size:30px;margin-bottom:13px}
.nv{font-family:'Amiri',serif;font-size:46px;color:var(--g);line-height:1;margin-bottom:8px}
.nl{font-size:11px;color:rgba(255,255,255,.36);letter-spacing:2px;text-transform:uppercase}
.dpts{background:var(--pa)}
.dg{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:48px}
.dc{background:#fff;border:1px solid #E8DEC8;border-radius:6px;overflow:hidden;transition:all .3s;cursor:pointer;position:relative}
.dc:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(0,0,0,.1);border-color:var(--g)}
.dct{height:4px;background:linear-gradient(90deg,var(--r),var(--g))}
.dc:hover .dct{background:linear-gradient(90deg,var(--g),var(--r))}
.dcb{padding:26px 22px 32px}
.dic{font-size:32px;margin-bottom:12px}
.dh{font-family:'Amiri',serif;font-size:19px;color:var(--k);margin-bottom:7px;line-height:1.3}
.dp{font-size:12px;color:var(--gr);line-height:1.8}
.dtg{display:inline-block;margin-top:13px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g);border:1px solid rgba(201,168,76,.28);padding:3px 10px;border-radius:2px}
.da{position:absolute;bottom:18px;left:18px;font-size:17px;color:var(--g);opacity:0;transition:opacity .25s,transform .25s;transform:translateX(6px)}
.dc:hover .da{opacity:1;transform:translateX(0)}
.mo{display:none;position:fixed;inset:0;z-index:800;background:rgba(8,4,2,.88);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:18px}
.mo.open{display:flex}
.mb{background:#1C1410;border:1px solid rgba(201,168,76,.3);border-radius:12px;padding:44px 40px;max-width:500px;width:100%;position:relative;animation:mi .32s ease;text-align:center}
@keyframes mi{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.mc{position:absolute;top:14px;left:18px;background:none;border:none;color:rgba(255,255,255,.38);font-size:20px;cursor:pointer;transition:.2s}
.mc:hover{color:var(--g)}
.mico{font-size:50px;margin-bottom:14px}
.mtt{font-family:'Amiri',serif;font-size:22px;color:#fff;margin-bottom:7px;line-height:1.35}
.ms{font-size:13px;color:rgba(255,255,255,.38);margin-bottom:28px}
.mchs{display:flex;flex-direction:column;gap:13px}
.mch{display:flex;align-items:center;gap:16px;background:rgba(255,255,255,.04);border:1px solid rgba(201,168,76,.18);border-radius:8px;padding:18px 20px;text-decoration:none;transition:all .25s;text-align:right}
.mch:hover{background:rgba(201,168,76,.1);border-color:rgba(201,168,76,.45);transform:translateX(-4px)}
.mci{font-size:30px;flex-shrink:0}
.mct{flex:1}
.mch2{font-family:'Amiri',serif;font-size:19px;color:#fff;margin-bottom:3px}
.mcp{font-size:12px;color:rgba(255,255,255,.42);line-height:1.5}
.mca{font-size:17px;color:var(--g);flex-shrink:0}
.tm{background:var(--k);position:relative;overflow:hidden}
.tm::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 80% 50%,rgba(201,168,76,.06),transparent 58%);pointer-events:none}
.ttc{display:flex;align-items:center;gap:36px;background:rgba(255,255,255,.04);border:1px solid rgba(201,168,76,.18);border-radius:8px;padding:42px 46px;position:relative;overflow:hidden;transition:border-color .3s,background .3s}
.ttc:hover{border-color:rgba(201,168,76,.42);background:rgba(255,255,255,.06)}
.ttci{font-size:66px;flex-shrink:0;animation:fl 3s ease-in-out infinite}
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
.ttcb{flex:1}
.ttch{font-family:'Amiri',serif;font-size:25px;color:#fff;margin-bottom:11px;line-height:1.3}
.ttcp{font-size:14px;color:rgba(255,255,255,.5);line-height:1.9;margin-bottom:26px;max-width:500px}
.ttcd{position:absolute;left:-8px;top:50%;transform:translateY(-50%);font-size:140px;opacity:.04;pointer-events:none;filter:grayscale(1)}
/* NEWS */
.news{background:var(--cr)}
.news-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:44px}
.news-grid .ncard{display:flex;flex-direction:column;max-width:none;height:100%}
.news-grid .nbar{width:100%;height:5px}
.news-grid .nimg{width:100%;height:220px;min-height:220px}
.news-grid .nbody{flex:1}
.ncard{display:flex;background:#fff;border:1px solid #E8DEC8;border-radius:6px;overflow:hidden;max-width:820px;transition:.3s}
.ncard:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(0,0,0,.09)}
.nbar{width:5px;background:linear-gradient(180deg,var(--r),var(--g));flex-shrink:0}
.nimg{width:260px;min-height:200px;flex-shrink:0;overflow:hidden}
.nimg img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
.ncard:hover .nimg img{transform:scale(1.04)}
.nbody{padding:24px 26px;display:flex;flex-direction:column}
.nmeta{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.ncat{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--r);font-weight:600}
.ndot{width:3px;height:3px;background:#ccc;border-radius:50%}
.ndate{font-size:11px;color:#999}
.nh{font-family:'Amiri',serif;font-size:21px;color:var(--k);line-height:1.4;margin-bottom:10px}
.nex{font-size:13px;color:var(--gr);line-height:1.8;margin-bottom:18px}
.nread{font-size:12px;color:var(--r);font-weight:600;letter-spacing:1px;text-transform:uppercase;background:none;border:none;cursor:pointer;padding:0;font-family:'Noto Kufi Arabic',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:gap .2s;margin-top:auto}
.nread:hover{gap:10px}
/* NEWS MODAL */
#news-modal{display:none;position:fixed;inset:0;z-index:800;background:rgba(8,4,2,.9);backdrop-filter:blur(7px);overflow-y:auto;padding:20px 16px}
.nmo-box{max-width:760px;margin:60px auto 40px;background:#fff;border-radius:10px;overflow:hidden;animation:mi .3s ease;position:relative}
.nmo-close{position:absolute;top:14px;left:16px;background:rgba(0,0,0,.08);border:none;border-radius:50%;width:34px;height:34px;font-size:17px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;z-index:10}
.nmo-close:hover{background:rgba(181,32,44,.15)}
.nmo-hero{width:100%;height:320px;overflow:hidden}
.nmo-hero img{width:100%;height:100%;object-fit:cover;object-position:center top}
.nmo-hero-ph{width:100%;height:280px;background:linear-gradient(135deg,#f0e8d4,#e8d4b0);display:flex;align-items:center;justify-content:center;font-size:72px}
.nmo-body{padding:36px 40px 44px}
.nmo-tag{font-size:10px;letter-spacing:2.5px;text-transform:uppercase;color:var(--r);font-weight:700}
.nmo-h{font-family:'Amiri',serif;font-size:clamp(20px,3.5vw,30px);color:var(--k);line-height:1.35;margin:14px 0 6px}
.nmo-dv{width:50px;height:2px;background:linear-gradient(90deg,var(--r),var(--g));border-radius:2px;margin-bottom:20px}
.nmo-txt{font-size:15px;color:#444;line-height:2;margin-bottom:22px}
.nmo-sub{font-family:'Amiri',serif;font-size:18px;color:var(--r);margin-bottom:28px}
/* NO NEWS */
.no-news{text-align:center;padding:60px 20px;color:var(--gr)}
.no-news-icon{font-size:52px;margin-bottom:16px;opacity:.3}
.no-news h3{font-family:'Amiri',serif;font-size:22px;color:var(--gr)}
/* STATS */
.stats-sec{background:var(--k);position:relative;overflow:hidden}
.stats-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(0,212,255,.05),transparent 60%),radial-gradient(ellipse at 80% 50%,rgba(123,97,255,.05),transparent 60%);pointer-events:none}
.stats-cta-wrap{text-align:center;margin-top:18px;padding:28px;background:rgba(255,255,255,.03);border:1px solid rgba(201,168,76,.15);border-radius:16px}
.stats-cta-title{font-family:'Amiri',serif;font-size:22px;color:#fff;margin-bottom:8px}
.stats-cta-sub{font-size:13px;color:rgba(255,255,255,.4);margin-bottom:22px}
.btn-dash{background:linear-gradient(135deg,#00d4ff,#7b61ff);color:#fff;padding:14px 32px;border-radius:6px;font-size:15px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:10px;transition:all .3s;box-shadow:0 6px 24px rgba(0,212,255,.3)}
.btn-dash:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,212,255,.45)}
img{image-rendering:auto;max-width:100%}
.ct{background:var(--k);position:relative;overflow:hidden}
.ct::before{content:'';position:absolute;top:0;right:0;width:460px;height:460px;background:radial-gradient(circle,rgba(181,32,44,.07),transparent 63%);pointer-events:none}
.cig{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:32px}
.ci{display:flex;gap:13px;align-items:flex-start;background:rgba(255,255,255,.04);border:1px solid rgba(201,168,76,.16);border-radius:8px;padding:20px 18px;transition:.25s}
.ci:hover{border-color:rgba(201,168,76,.38);background:rgba(255,255,255,.06)}
.cic{width:40px;height:40px;flex-shrink:0;background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.2);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:17px}
.cit{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g);margin-bottom:3px}
.civ{font-size:13px;color:rgba(255,255,255,.6);line-height:1.7}
footer{background:#0F0C0A;border-top:1px solid rgba(201,168,76,.1);padding:50px 52px 24px}
.fg{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:50px;max-width:1200px;margin:0 auto 38px}
.flo{display:flex;align-items:center;gap:11px;margin-bottom:13px}
.flb{width:36px;height:36px;background:var(--r);border:1px solid var(--g);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:17px}
.fln{font-family:'Amiri',serif;font-size:16px;color:var(--g)}
.flt{font-size:13px;color:rgba(255,255,255,.26);line-height:1.8;max-width:250px}
.fc h4{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--g);margin-bottom:15px}
.fc ul{list-style:none;display:flex;flex-direction:column;gap:9px}
.fc ul a{color:rgba(255,255,255,.3);text-decoration:none;font-size:13px;transition:.2s}
.fc ul a:hover{color:var(--g)}
.fbt{border-top:1px solid rgba(255,255,255,.05);padding-top:20px;display:flex;justify-content:space-between;align-items:center;max-width:1200px;margin:0 auto;flex-wrap:wrap;gap:10px}
.fbt p{font-size:11px;color:rgba(255,255,255,.17)}
@media(max-width:1024px){
  nav{padding:13px 20px}.nu{display:none}.bg{display:flex}
  section{padding:58px 20px}.hc{padding:0 20px;padding-top:88px}
  .ag{grid-template-columns:1fr;gap:52px}.abd{right:14px}
  .ncard{flex-direction:column}.nimg{width:100%;height:220px}
  .news-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
  .ttc{flex-direction:column;text-align:center;padding:30px 20px;gap:16px}.ttcd{display:none}.ttcp{max-width:100%}
  .cig{grid-template-columns:1fr 1fr}
  .fg{grid-template-columns:1fr 1fr;gap:32px}
}
@media(max-width:600px){
  .hst{gap:18px}.hsn{font-size:30px}
  .fts{grid-template-columns:1fr}
  .dg{grid-template-columns:1fr}
  .news-grid{grid-template-columns:1fr}
  .cig{grid-template-columns:1fr}
  footer{padding:34px 16px 18px}.fg{grid-template-columns:1fr}.fbt{flex-direction:column;text-align:center}
  .nmo-body{padding:24px 20px 32px}.nmo-hero{height:220px}
}
</style>
</head>
<body>
<div id="ld"><div class="lr"></div><div class="lt">الثانوية التأهيلية عمرو بن العاص</div><div class="lb"><span class="lf"></span></div></div>
<div id="mob">
<button class="xb" id="xb">✕</button>
<a class="ml" href="#home">الرئيسية</a>
<a class="ml" href="#about">التعريف</a>
<a class="ml" href="#announcements">الإعلانات</a>
<a class="ml" href="#departments">الشعب</a>
<a class="ml" href="#timetable">استعمال الزمن</a>
<a class="ml" href="#news">الأخبار</a>
<a class="ml" href="#stats">الإحصائيات</a>
<a class="ml" href="#contact">التواصل</a>
</div>
<header id="hdr">
<nav>
<a class="logo" href="#home">
<div class="lbox" style="padding:0;overflow:hidden;width:88px;height:44px;border-radius:6px;"><img loading="eager" fetchpriority="high" src="imgs/img1.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:6px;"/></div>
<div><div class="lar">الثانوية عمرو بن العاص التأهيلية</div><div class="lfr">Lycée Qualifiant · Maroc</div></div>
</a>
<ul class="nu">
<li><a href="#about">التعريف</a></li><li><a href="#announcements">الإعلانات</a></li>
<li><a href="#departments">الشعب</a></li>
<li><a href="#timetable">استعمال الزمن</a></li>
<li><a href="#news">الأخبار</a></li>
<li><a href="#stats">الإحصائيات</a></li>
<li><a class="ct" href="#contact">تواصل معنا</a></li>
</ul>
<div class="bg" id="bg"><span></span><span></span><span></span></div>
</nav>
</header>

<section class="hero" id="home">
<div class="hbg" id="hbg"></div>
<div class="ho"></div><div class="ho2"></div><div class="hp"></div>
<div class="hc">
<div class="ew"><div class="el"></div><div class="et">الأكاديمية الجهوية للتربية والتكوين لجهة سوس ماسة  |  المديرية الإقليمية لأكادير إداوتنان</div></div>
<h1 class="hh">الثانوية التأهيلية<br/><span>عمرو بن العاص</span></h1>
<p class="hfr">LYCÉE QUALIFIANT AMROU BNOU LAAS</p>
<div class="hst">
<div class="hs"><div class="hsn">1200+</div><div class="hsl">عدد التلاميذ</div></div>
<div class="hs"><div class="hsn">85%</div><div class="hsl">نسبة النجاح</div></div>
<div class="hs"><div class="hsn">70+</div><div class="hsl">عدد الموظفين</div></div>
</div>
<div class="hbt">
<a class="btn bg2" href="#about">اكتشف المؤسسة</a>
<a class="btn bgh" href="#contact">تواصل معنا</a>
</div>
</div>
<div class="scue"><svg fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24" width="15"><path d="M12 5v14M5 12l7 7 7-7"></path></svg><span>اكتشف</span></div>
</section>

<div class="tk"><div class="ti">
<span class="tit">🎓 مرحباً بكم في الثانوية التأهيلية عمرو بن العاص</span><span class="tit ts">✦</span>
<span class="tit">التسجيل للموسم الدراسي 2025-2026 مفتوح الآن</span><span class="tit ts">✦</span>
<span class="tit">Bienvenue au Lycée Qualifiant Amrou Bnou Laas</span><span class="tit ts">✦</span>
<span class="tit">🎓 مرحباً بكم في الثانوية التأهيلية عمرو بن العاص</span><span class="tit ts">✦</span>
<span class="tit">التسجيل للموسم الدراسي 2025-2026 مفتوح الآن</span><span class="tit ts">✦</span>
<span class="tit">Bienvenue au Lycée Qualifiant Amrou Bnou Laas</span>
</div></div>

<section class="anns" id="announcements">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">الإعلانات</div></div><h2 class="sh">الإعلانات الرسمية</h2><div class="dv"></div><p class="sp">جميع الإعلانات الإدارية ومواعيد الامتحانات الخاصة بالمؤسسة.</p></div>
<div class="ann-carousel-wrap rv d1">
<div class="ann-carousel-track" id="annTrack">
<div class="ann-carousel-slide"><div class="ann-card"><div class="ann-card-top adm"></div><div class="ann-card-body"><div class="ann-card-meta"><span class="ann-card-tag adm">إداري</span><span class="ann-card-date">📅 23 أبريل 2026</span></div><div class="ann-card-title">هام لتلاميذ الثانية باكالوريا (2026)</div><div class="ann-card-text">🔴 المرجو من جميع المترشحين استخراج مطبوع تدقيق المعطيات وإيداعه لدى الحراسة العامة قبل متم يوم الخميس 23 أبريل 2026.</div></div></div></div>
<div class="ann-carousel-slide"><div class="ann-card"><div class="ann-card-top" style="background:linear-gradient(90deg,#1a5e2a,#4caf50)"></div><div class="ann-card-body"><div class="ann-card-meta"><span class="ann-card-tag exam">التسجيلات</span><span class="ann-card-date">📅 2025–2026</span></div><div class="ann-card-title">📚 التسجيل للموسم الدراسي 2025-2026 مفتوح الآن</div><div class="ann-card-text">يمكن للتلاميذ الجدد وأوليائهم التوجه إلى الإدارة لإتمام إجراءات التسجيل.</div></div></div></div>
<div class="ann-carousel-slide"><div class="ann-card"><div class="ann-card-top adm"></div><div class="ann-card-body"><div class="ann-card-meta"><span class="ann-card-tag adm">استعمال الزمن</span><span class="ann-card-date">📅 2025–2026</span></div><div class="ann-card-title">📅 جداول التوقيت الرسمية متاحة للتحميل</div><div class="ann-card-text">يمكنكم الاطلاع على الجداول الزمنية الرسمية لجميع المستويات والشعب الدراسية للموسم الدراسي 2025-2026.</div></div></div></div>
</div>
<div class="ann-ctrl">
<button class="ann-ctrl-btn" id="annPrev">›</button>
<div class="ann-dots" id="annDots"><button class="ann-dot active" data-i="0"></button><button class="ann-dot" data-i="1"></button><button class="ann-dot" data-i="2"></button></div>
<button class="ann-ctrl-btn" id="annNext">‹</button>
</div>
<div class="ann-progress-bar"><div class="ann-progress-fill" id="annFill"></div></div>
</div>
</div>
</section>

<section class="about" id="about">
<div class="wrap"><div class="ag">
<div class="aiw rv"><img decoding="async" alt="مدخل الثانوية" class="aim" id="aimg" loading="lazy" src=""/><div class="abd"><div class="abn">2019</div><div class="abl">التأسيس</div></div></div>
<div>
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">من نحن</div></div><h2 class="sh">التعريف بالمؤسسة</h2><div class="dv"></div></div>
<p class="sp rv d2">تُعَدّ الثانوية التأهيلية عمرو بن العاص إحدى مؤسسات المديرية الإقليمية لأكادير إداوتنان، التابعة للأكاديمية الجهوية للتربية والتكوين لجهة سوس ماسة.</p>
<div class="rv d2" style="margin:18px 0 6px">
<div class="sl"><div class="sl-l"></div><div class="sl-t">الروافد</div></div>
<div style="display:flex;flex-direction:column;gap:8px;">
<div class="ft">🏫 إعدادية رام الله</div>
<div class="ft">🏫 إعدادية جمال الدين الأفغاني</div>
<div class="ft">🏫 إعدادية العرفان</div>
</div></div>
</div></div></div>
</section>

<section class="nbs">
<div class="wrap">
<div class="nctr"><div class="sl rv" style="justify-content:center"><div class="sl-l"></div><div class="sl-t">بالأرقام</div><div class="sl-l"></div></div><h2 class="sh lt rv">المؤسسة في أرقام</h2></div>
<div class="ng rv">
<div class="nc"><div class="ni">🎓</div><div class="nv">1200+</div><div class="nl">عدد التلاميذ</div></div>
<div class="nc"><div class="ni">📈</div><div class="nv">85%</div><div class="nl">نسبة النجاح</div></div>
<div class="nc"><div class="ni">👩‍🏫</div><div class="nv">70+</div><div class="nl">عدد الموظفين</div></div>
<div class="nc"><div class="ni">📅</div><div class="nv">2019</div><div class="nl">سنة التأسيس</div></div>
</div></div>
</section>

<section class="dpts" id="departments">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">التخصصات</div></div><h2 class="sh">الشعب والمسالك الدراسية</h2><div class="dv"></div><p class="sp">اضغط على أي شعبة للاطلاع على الامتحانات والدروس.</p></div>
<div class="dg">
<div class="dc rv d1" onclick="openM('2bac-svt')"><div class="dct"></div><div class="dcb"><div class="dic">🧬</div><h3 class="dh">الثانية باك علوم الحياة والأرض</h3><p class="dp">بيولوجيا وجيولوجيا وعلوم الطبيعة — نحو الطب والصيدلة.</p><span class="dtg">2ème Bac SVT</span><div class="da">←</div></div></div>
<div class="dc rv d2" onclick="openM('2bac-pc')"><div class="dct"></div><div class="dcb"><div class="dic">⚛️</div><h3 class="dh">الثانية باك العلوم الفيزيائية والكيمياء</h3><p class="dp">فيزياء وكيمياء تطبيقية — نحو الهندسة والعلوم التطبيقية.</p><span class="dtg">2ème Bac PC</span><div class="da">←</div></div></div>
<div class="dc rv d3" onclick="openM('2bac-lettres')"><div class="dct"></div><div class="dcb"><div class="dic">📚</div><h3 class="dh">الثانية باك الآداب</h3><p class="dp">أدب وفلسفة ولغات — نحو الدراسات الإنسانية والقانونية.</p><span class="dtg">2ème Bac Lettres</span><div class="da">←</div></div></div>
<div class="dc rv d4" onclick="openM('1bac-exp')"><div class="dct"></div><div class="dcb"><div class="dic">🔬</div><h3 class="dh">الأولى باك العلوم التجريبية</h3><p class="dp">علوم تجريبية شاملة تمهيداً للشعب العلمية في الثانية باك.</p><span class="dtg">1ère Bac Sciences Exp.</span><div class="da">←</div></div></div>
<div class="dc rv d1" onclick="openM('1bac-sh')"><div class="dct"></div><div class="dcb"><div class="dic">🌎</div><h3 class="dh">الأولى باك العلوم الإنسانية</h3><p class="dp">تاريخ وجغرافيا وفلسفة — تمهيداً للمسار الأدبي.</p><span class="dtg">1ère Bac Sciences Humaines</span><div class="da">←</div></div></div>
<div class="dc rv d2" onclick="openM('tc-sci')"><div class="dct"></div><div class="dcb"><div class="dic">📐</div><h3 class="dh">الجذع المشترك العلمي</h3><p class="dp">السنة الأولى من التعليم الثانوي التأهيلي — المسار العلمي.</p><span class="dtg">Tronc Commun Scientifique</span><div class="da">←</div></div></div>
<div class="dc rv d3" onclick="openM('tc-lit')"><div class="dct"></div><div class="dcb"><div class="dic">✍️</div><h3 class="dh">الجذع المشترك الأدبي</h3><p class="dp">السنة الأولى من التعليم الثانوي التأهيلي — المسار الأدبي.</p><span class="dtg">Tronc Commun Littéraire</span><div class="da">←</div></div></div>
</div></div>
</section>

<section class="tm" id="timetable">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">التنظيم الدراسي</div></div><h2 class="sh lt">استعمال الزمن</h2><div class="dv"></div><p class="sp lt">يمكنكم الاطلاع على الجداول الزمنية الرسمية للموسم الدراسي 2025-2026.</p></div>
<div class="ttc rv d1">
<div class="ttci">📅</div>
<div class="ttcb">
<h3 class="ttch">جداول التوقيت الرسمية — الموسم الدراسي 2025-2026</h3>
<p class="ttcp">الجدول الزمني المعتمد لجميع المستويات والشعب الدراسية بالثانوية التأهيلية عمرو بن العاص.</p>
<a class="btn bg2" href="https://drive.google.com/file/d/1mNKOi3T_MWSPZx6aMfhtZMHKsZnPV0yX/view?usp=drivesdk" target="_blank">📄 عرض استعمال الزمن</a>
</div>
<div class="ttcd">📅</div>
</div>
</div>
</section>

<!-- ===== قسم الأخبار — ديناميكي من PHP ===== -->
<section class="news" id="news">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">الأخبار</div></div><h2 class="sh">آخر المستجدات والفعاليات</h2><div class="dv"></div></div>

<?php if (empty($newsToShow)): ?>
<div class="no-news rv">
  <div class="no-news-icon">📰</div>
  <h3>لا توجد أخبار منشورة حالياً</h3>
  <p style="margin-top:8px;font-size:13px">سيتم إضافة الأخبار قريباً</p>
</div>
<?php else: ?>
<div class="news-grid">
<?php
$delays = ['d1','d2','d3','d4'];
foreach ($newsToShow as $i => $n):
  $delay = $delays[$i % 4];
  $fnId  = 'news-modal';
  $nId   = (int)$n['id'];
?>
<div class="ncard rv <?= $delay ?>">
  <div class="nbar"></div>
  <div class="nimg">
    <?php if (!empty($n['image'])): ?>
    <img decoding="async" alt="<?= h($n['title']) ?>" loading="lazy" src="<?= h($n['image']) ?>"/>
    <?php else: ?>
    <div style="width:100%;height:100%;background:linear-gradient(135deg,#f0e8d4,#e8d4b0);display:flex;align-items:center;justify-content:center;font-size:44px">📰</div>
    <?php endif; ?>
  </div>
  <div class="nbody">
    <div class="nmeta">
      <span class="ncat"><?= h($n['category']) ?></span>
      <span class="ndot"></span>
      <span class="ndate"><?= h($n['date']) ?></span>
    </div>
    <h3 class="nh"><?= h($n['title']) ?></h3>
    <p class="nex"><?= h($n['excerpt'] ?? '') ?></p>
    <button class="nread" onclick="openNewsModal(<?= $nId ?>)">اقرأ المزيد ←</button>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>

<!-- ===== نافذة الخبر — مشتركة لكل الأخبار ===== -->
<div id="news-modal">
  <div class="nmo-box">
    <button class="nmo-close" onclick="closeNewsModal()">✕</button>
    <div id="nmo-img-wrap"></div>
    <div class="nmo-body">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="nmo-tag" id="nmo-cat">—</span>
        <span style="width:3px;height:3px;background:#ccc;border-radius:50%;display:inline-block;margin:0 4px"></span>
        <span style="font-size:12px;color:#aaa" id="nmo-date">—</span>
      </div>
      <h2 class="nmo-h" id="nmo-title">—</h2>
      <div class="nmo-dv"></div>
      <div id="nmo-content"></div>
    </div>
  </div>
</div>

<!-- بيانات الأخبار لـ JS -->
<script>
var NEWS_DATA = <?= json_encode(array_values($published), JSON_UNESCAPED_UNICODE) ?>;

function openNewsModal(id) {
  var n = NEWS_DATA.find(function(x){ return x.id == id; });
  if (!n) return;
  document.getElementById('nmo-cat').textContent   = n.category || '';
  document.getElementById('nmo-date').textContent  = n.date || '';
  document.getElementById('nmo-title').textContent = n.title || '';
  // content — split by newlines into paragraphs
  var content = (n.content || '').split('\n').filter(function(l){ return l.trim(); });
  document.getElementById('nmo-content').innerHTML = content.map(function(l){
    return '<p class="nmo-txt">' + escHtml(l.trim()) + '</p>';
  }).join('');
  // image
  var wrap = document.getElementById('nmo-img-wrap');
  if (n.image) {
    var img = document.createElement('div');
    img.className = 'nmo-hero';
    img.innerHTML = '<img src="'+escHtml(n.image)+'" alt="'+escHtml(n.title)+'" style="width:100%;height:100%;object-fit:cover" onerror="this.parentElement.innerHTML=\'<div class=nmo-hero-ph>📰</div>\'"/>';
    wrap.innerHTML = '';
    wrap.appendChild(img);
  } else {
    wrap.innerHTML = '<div class="nmo-hero-ph">📰</div>';
  }
  document.getElementById('news-modal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeNewsModal() {
  document.getElementById('news-modal').style.display = 'none';
  document.body.style.overflow = '';
}

document.getElementById('news-modal').addEventListener('click', function(e){
  if (e.target === this) closeNewsModal();
});

function escHtml(s) {
  var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML;
}
</script>

<section class="stats-sec" id="stats">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">نتائج الموسم الدراسي</div></div><h2 class="sh lt">الإحصائيات التربوية 2025-2026</h2><div class="dv"></div><p class="sp lt">نظرة شاملة على نتائج وأداء المؤسسة — الموسم الدراسي الحالي.</p></div>
<div class="stats-cta-wrap rv">
<div class="stats-cta-title">لوحة الإحصائيات التفصيلية</div>
<div class="stats-cta-sub">اطلع على تحليل كامل لنتائج كل شعبة وكل قسم — مع رسوم بيانية تفاعلية</div>
<a class="btn-dash" href="dashboard.html" target="_blank">📊 فتح لوحة الإحصائيات الكاملة ←</a>
</div>
</div>
</section>

<section class="ct" id="contact">
<div class="wrap">
<div class="rv"><div class="sl"><div class="sl-l"></div><div class="sl-t">التواصل</div></div><h2 class="sh lt">تواصل معنا</h2><div class="dv"></div><p class="sp lt">يسعدنا استقبال استفساراتكم. فريقنا الإداري في خدمتكم.</p></div>
<div class="cig rv d1">
<div class="ci"><div class="cic">📍</div><div><div class="cit">العنوان</div><div class="civ">Av. Aït Moulay, Anza 80000 — Agadir</div></div></div>
<div class="ci"><div class="cic">📞</div><div><div class="cit">الهاتف</div><div class="civ">0537 - XXX - XXX</div></div></div>
<div class="ci"><div class="cic">✉</div><div><div class="cit">البريد الإلكتروني</div><div class="civ">lycee.amroubnolaas@gmail.com</div></div></div>
<div class="ci"><div class="cic">⏰</div><div><div class="cit">أوقات العمل</div><div class="civ">الإثنين – السبت: 08:30 – 18:30</div></div></div>
</div>
</div>
</section>

<footer>
<div class="fg">
<div><div class="flo"><div class="flb">🏫</div><div class="fln">عمرو بن العاص</div></div>
<p class="flt">مؤسسة تعليمية عريقة تسعى إلى تكوين أجيال متميزة مؤمنة بقيم الوطن ومسلحة بالعلم والمعرفة.</p>
<div class="fsoc">
<a class="fsoc-a fsoc-fb" href="https://www.facebook.com/profile.php?id=61557664534201" target="_blank">
<svg fill="currentColor" height="16" viewbox="0 0 24 24" width="16"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
</a>
</div>
</div>
</div>
<div class="fbt"><p>© ٢٠٢٦ الثانوية التأهيلية عمرو بن العاص — جميع الحقوق محفوظة</p><span>🇲🇦</span><p>Lycée Qualifiant Amrou Bnou Laas — Maroc</p></div>
</footer>

<div style="background:#0a0806;border-top:1px solid rgba(201,168,76,.1);padding:13px;text-align:center">
<p style="font-size:12px;color:rgba(255,255,255,.35)">Développement et Support Technique : <span style="color:rgba(201,168,76,.65);font-weight:600">Mohamed Ouchen</span></p>
</div>

<script>
(function(){
  document.getElementById('hbg').style.backgroundImage='url("imgs/img4.png")';
  document.getElementById('aimg').src='imgs/img4.png';
  window.addEventListener('load',function(){setTimeout(function(){var l=document.getElementById('ld');if(l)l.style.display='none';},800);});
  window.addEventListener('scroll',function(){var h=document.getElementById('hdr');if(h)h.classList.toggle('sc',window.scrollY>60);});
  var bg=document.getElementById('bg'),mob=document.getElementById('mob'),xb=document.getElementById('xb');
  if(bg)bg.addEventListener('click',function(){mob.classList.add('open');});
  if(xb)xb.addEventListener('click',function(){mob.classList.remove('open');});
  document.querySelectorAll('.ml').forEach(function(a){a.addEventListener('click',function(){mob.classList.remove('open');});});
  var rvs=document.querySelectorAll('.rv');
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(en){en.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}});},{threshold:0.1});
    rvs.forEach(function(el){io.observe(el);});
  } else {rvs.forEach(function(el){el.classList.add('in');});}
  var bdata={'tc-sci':'https://telmidtice.men.gov.ma/courses?level=3&category=67c59d79062e69f770de725a&subCategory=67c59d7a062e69f770de72ab','tc-lit':'https://telmidtice.men.gov.ma/courses?level=3&category=67c59d79062e69f770de725a&subCategory=67c59d7a062e69f770de72b3','1bac-exp':'https://telmidtice.men.gov.ma/courses?level=3&category=67c6fbf06dc6d7814a59423b&subCategory=67c6fcb52852c37f346d382d','1bac-sh':'https://telmidtice.men.gov.ma/courses?level=3&category=67c6fbf06dc6d7814a59423b&subCategory=67c6fcb52852c37f346d3831','2bac-pc':'https://telmidtice.men.gov.ma/courses?level=3&category=67c6fa462852c37f346d37ae&subCategory=67c6fcb52852c37f346d382f','2bac-svt':'https://telmidtice.men.gov.ma/courses?level=3&category=67c6fa462852c37f346d37ae&subCategory=67c6fcb52852c37f346d382b','2bac-lettres':'https://telmidtice.men.gov.ma/courses?level=3&category=67c6fa462852c37f346d37ae&subCategory=67dbf8692d3e5329c49de9a5'};
  window.openM=function(id){var b=bdata[id];if(b)window.open(b,'_blank');};
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeNewsModal();});
  // Carousel
  (function(){
    var track=document.getElementById('annTrack'),dots=Array.from(document.querySelectorAll('.ann-dot')),prevBtn=document.getElementById('annPrev'),nextBtn=document.getElementById('annNext'),fill=document.getElementById('annFill'),total=dots.length,cur=0,dur=4200,timer,fillTimer;
    function goTo(i){cur=(i+total)%total;track.style.transform='translateX('+(cur*100)+'%)';dots.forEach(function(d,idx){d.classList.toggle('active',idx===cur);});resetFill();}
    function resetFill(){if(!fill)return;fill.style.transition='none';fill.style.width='0%';clearTimeout(fillTimer);fillTimer=setTimeout(function(){fill.style.transition='width '+dur+'ms linear';fill.style.width='100%';},30);}
    function startAuto(){clearInterval(timer);timer=setInterval(function(){goTo(cur+1);},dur);resetFill();}
    function stopAuto(){clearInterval(timer);if(fill){fill.style.transition='none';fill.style.width=fill.getBoundingClientRect().width+'px';}}
    if(prevBtn)prevBtn.addEventListener('click',function(){goTo(cur-1);stopAuto();startAuto();});
    if(nextBtn)nextBtn.addEventListener('click',function(){goTo(cur+1);stopAuto();startAuto();});
    dots.forEach(function(d){d.addEventListener('click',function(){goTo(parseInt(d.getAttribute('data-i'),10));stopAuto();startAuto();});});
    if(total>0)startAuto();
  })();
})();
</script>
</body>
</html>
