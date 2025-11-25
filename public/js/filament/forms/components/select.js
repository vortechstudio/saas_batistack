var tt=Math.min,H=Math.max,et=Math.round;var E=s=>({x:s,y:s}),Gt={left:"right",right:"left",bottom:"top",top:"bottom"},Qt={start:"end",end:"start"};/**
 * Renvoie le maximum entre s et le minimum de t et e.
 * @param {number} s - Valeur de référence comparée au minimum de t et e.
 * @param {number} t - Première borne utilisée pour calculer le minimum.
 * @param {number} e - Seconde borne utilisée pour calculer le minimum.
 * @returns {number} La valeur la plus grande entre s et le minimum de t et e.
 */
function mt(s,t,e){return H(s,tt(t,e))}/**
 * Obtient soit la valeur fournie, soit le résultat de l'appel de cette valeur si c'est une fonction.
 * @param {*} s - Valeur ou fonction ; si c'est une fonction, elle sera appelée avec `t`.
 * @param {*} t - Argument à transmettre si `s` est une fonction.
 * @returns {*} Le résultat de `s(t)` quand `s` est une fonction, sinon la valeur `s`.
 */
function it(s,t){return typeof s=="function"?s(t):s}/**
 * Extrait la partie avant le premier tiret d'une chaîne de placement.
 * @param {string} s - Chaîne de placement (par ex. "top-start", "bottom-end" ou "left").
 * @returns {string} La sous-chaîne située avant le premier tiret ; si aucun tiret est présent, retourne la chaîne entière.
 */
function z(s){return s.split("-")[0]}/**
 * Retourne la portion de chaîne située après le premier tiret.
 * @param {string} s - Chaîne contenant deux segments séparés par un tiret (ex. "top-start").
 * @returns {string|undefined} La sous-chaîne après le premier tiret, ou `undefined` si aucun tiret n'est présent.
 */
function st(s){return s.split("-")[1]}/**
 * Renvoie l'axe complémentaire à l'axe fourni.
 * @param {string} s - Axe source, attendu 'x' ou 'y'.
 * @returns {string} `'y'` si `s` vaut `'x'`, `'x'` dans les autres cas.
 */
function gt(s){return s==="x"?"y":"x"}/**
 * Retourne le nom de la propriété de dimension correspondant à un axe.
 * @param {string} s - Axe attendu ('x' ou 'y').
 * @returns {string} `"height"` si `s` vaut `"y"`, `"width"` sinon.
 */
function bt(s){return s==="y"?"height":"width"}var Zt=new Set(["top","bottom"]);/**
 * Détermine l'axe principal associé à un placement.
 * @param {string} s - Chaîne de placement (par ex. "top", "bottom-start", "left-end").
 * @returns {'y'|'x'} 'y' si le placement est "top" ou "bottom", 'x' sinon.
 */
function M(s){return Zt.has(z(s))?"y":"x"}/**
 * Détermine l'axe croisé correspondant à un placement donné.
 * @param {string} s - Chaîne de placement (par ex. "top", "bottom", "left", "right", éventuellement avec "-start" ou "-end").
 * @returns {'x'|'y'} Le nom de l'axe croisé : `'x'` pour l'axe horizontal, `'y'` pour l'axe vertical.
 */
function yt(s){return gt(M(s))}/**
 * Détermine l'arête principale et son opposé pour un placement donné en fonction des rectangles de référence et flottant.
 * @param {string} s - Placement au format "side-alignment" (ex. "top-start", "right-end").
 * @param {{reference: {x:number,y:number,width:number,height:number,top:number,left:number,right:number,bottom:number}, floating: {x:number,y:number,width:number,height:number,top:number,left:number,right:number,bottom:number}}} t - Objets rectangulaires de la référence et de l'élément flottant.
 * @param {boolean} [e=false] - Indique si l'orientation start/end doit être interprétée en mode RTL.
 * @returns {string[]} Un tableau de deux éléments : [arêtePrimale, arêteOpposée] (ex. ["left","right"]).
 */
function Ot(s,t,e){e===void 0&&(e=!1);let i=st(s),n=yt(s),o=bt(n),r=n==="x"?i===(e?"end":"start")?"right":"left":i==="start"?"bottom":"top";return t.reference[o]>t.floating[o]&&(r=Z(r)),[r,Z(r)]}/**
 * Renvoie trois placements dérivés à partir d'un placement donné.
 * @param {string} s - Placement initial (par exemple "top", "bottom-start", "left-end").
 * @returns {string[]} Tableau contenant trois placements : 1) le placement principal ajusté, 2) le placement opposé, 3) l'ajustement du placement opposé. 
 */
function At(s){let t=Z(s);return[lt(s),t,lt(t)]}/**
 * Remplace toutes les occurrences de "start" et "end" dans la chaîne par leurs équivalents définis dans Qt.
 * @param {string} s - Chaîne de placement ou d'alignement contenant éventuellement "start" et/ou "end".
 * @returns {string} La chaîne résultante avec les remplacements appliqués.
 */
function lt(s){return s.replace(/start|end/g,t=>Qt[t])}var vt=["left","right"],Lt=["right","left"],te=["top","bottom"],ee=["bottom","top"];/**
 * Détermine la liste d'arêtes prioritaires pour un placement donné.
 *
 * Retourne un tableau d'arêtes ("left"/"right" ou "top"/"bottom") ordonné selon les drapeaux fournis.
 *
 * @param {string} s - Placement principal attendu : "top", "bottom", "left" ou "right".
 * @param {boolean} t - Si `true`, retourne l'ordre prioritaire normal (premier élément préféré) ; si `false`, inverse l'ordre.
 * @param {boolean} e - Pour les placements verticaux ("top"/"bottom"), si `true` utilise l'inversion RTL/alternative des arêtes.
 * @returns {string[]} Tableau d'arêtes ordonné selon `s`, `t` et `e`. Renvoie un tableau vide si `s` n'est pas reconnu.
 */
function ie(s,t,e){switch(s){case"top":case"bottom":return e?t?Lt:vt:t?vt:Lt;case"left":case"right":return t?te:ee;default:return[]}}/**
 * Construit la liste des placements alignés possibles pour un placement donné en tenant compte des variations start/end et de la symétrie RTL.
 *
 * @param {string} s - Placement source (par ex. `"top"`, `"bottom-start"`, `"left-end"`).
 * @param {boolean} t - Si vrai, inclut également les variantes miroir RTL de chaque placement calculé.
 * @param {string|undefined} e - Préférence d'alignement : `"start"`, `"end"` ou `undefined` pour aucune préférence explicite.
 * @param {boolean} i - Indique si les options d'arêtes (start/end) doivent être générées comme alternatives.
 * @returns {string[]|undefined} Tableau des placements dérivés (par ex. `["top-start","top-end"]`) ou `undefined` si aucun alignement n'est applicable.
 */
function St(s,t,e,i){let n=st(s),o=ie(z(s),e==="start",i);return n&&(o=o.map(r=>r+"-"+n),t&&(o=o.concat(o.map(lt)))),o}/**
 * Remplace dans une chaîne les occurrences "left", "right", "top" et "bottom" par leurs côtés opposés.
 * @param {string} s - Chaîne contenant des placements (par ex. "top-start", "right").
 * @returns {string} La chaîne avec chaque occurrence de "left", "right", "top" et "bottom" remplacée par son opposé.
 */
function Z(s){return s.replace(/left|right|bottom|top/g,t=>Gt[t])}/**
 * Fusionne un objet d'insets partiel avec des valeurs par défaut de 0 pour chaque côté.
 * @param {{top?: number, right?: number, bottom?: number, left?: number}|undefined} s - Objet contenant un ou plusieurs des champs `top`, `right`, `bottom`, `left`. Les champs absents seront considérés comme 0.
 * @return {{top: number, right: number, bottom: number, left: number}} Objet complet d'insets avec `top`, `right`, `bottom` et `left` définis.
 */
function se(s){return{top:0,right:0,bottom:0,left:0,...s}}/**
 * Normalise une valeur d'inset en un objet {top,right,bottom,left}.
 * @param {(number|object)} s - Valeur d'inset. Si c'est un nombre, tous les côtés prendront cette valeur ; sinon un objet partiel d'insets {top,right,bottom,left} est attendu.
 * @returns {{top:number,right:number,bottom:number,left:number}} Un objet d'insets avec les quatre côtés renseignés.
 */
function Ct(s){return typeof s!="number"?se(s):{top:s,right:s,bottom:s,left:s}}/**
 * Normalise un objet rectangulaire en un rect standard contenant les propriétés de position et de taille.
 * @param {Object} s - Objet source décrivant un rectangle. Doit contenir `x`, `y`, `width` et `height`.
 * @returns {Object} Un objet rect standard avec les champs `x`, `y`, `width`, `height`, `top`, `left`, `right` et `bottom`.
 */
function U(s){let{x:t,y:e,width:i,height:n}=s;return{width:i,height:n,top:e,left:t,right:t+i,bottom:e+n,x:t,y:e}}/**
 * Calcule les coordonnées initiales d'un élément flottant en fonction d'un placement donné par rapport à un élément de référence.
 *
 * @param {{reference: {x:number,y:number,width:number,height:number}, floating: {width:number,height:number}}} s - Objet contenant les rectangles de référence et flottant. Seules les propriétés listées sont requises.
 * @param {string} t - Chaîne de placement (par ex. "top", "bottom-start", "left-end") décrivant la position désirée.
 * @param {boolean} e - Indique si le contexte est en RTL (right-to-left).
 * @returns {{x:number,y:number}} Objet contenant les coordonnées `x` et `y` en pixels pour positionner l'élément flottant. */
function Dt(s,t,e){let{reference:i,floating:n}=s,o=M(t),r=yt(t),l=bt(r),a=z(t),c=o==="y",h=i.x+i.width/2-n.width/2,d=i.y+i.height/2-n.height/2,p=i[l]/2-n[l]/2,f;switch(a){case"top":f={x:h,y:i.y-n.height};break;case"bottom":f={x:h,y:i.y+i.height};break;case"right":f={x:i.x+i.width,y:d};break;case"left":f={x:i.x-n.width,y:d};break;default:f={x:i.x,y:i.y}}switch(st(t)){case"start":f[r]-=p*(e&&c?-1:1);break;case"end":f[r]+=p*(e&&c?-1:1);break}return f}var Et=async(s,t,e)=>{let{placement:i="bottom",strategy:n="absolute",middleware:o=[],platform:r}=e,l=o.filter(Boolean),a=await(r.isRTL==null?void 0:r.isRTL(t)),c=await r.getElementRects({reference:s,floating:t,strategy:n}),{x:h,y:d}=Dt(c,i,a),p=i,f={},u=0;for(let m=0;m<l.length;m++){let{name:g,fn:w}=l[m],{x:b,y:v,data:L,reset:x}=await w({x:h,y:d,initialPlacement:i,placement:p,strategy:n,middlewareData:f,rects:c,platform:r,elements:{reference:s,floating:t}});h=b??h,d=v??d,f={...f,[g]:{...f[g],...L}},x&&u<=50&&(u++,typeof x=="object"&&(x.placement&&(p=x.placement),x.rects&&(c=x.rects===!0?await r.getElementRects({reference:s,floating:t,strategy:n}):x.rects),{x:h,y:d}=Dt(c,p,a)),m=-1)}return{x:h,y:d,placement:p,strategy:n,middlewareData:f}};/**
 * Calcule les distances de débordement entre l'élément flottant et sa zone de découpe.
 *
 * @param {Object} s - Contexte de positionnement.
 * @param {number} s.x - Coordonnée x calculée du floating.
 * @param {number} s.y - Coordonnée y calculée du floating.
 * @param {Object} s.platform - Implémentation platform fournie (doit exposer getClippingRect, getOffsetParent, getScale, convertOffsetParentRelativeRectToViewportRelativeRect, isElement, getDocumentElement).
 * @param {Object} s.rects - Rectangles sources { reference, floating } (largeur/hauteur incluses).
 * @param {Object} s.elements - Références DOM { reference, floating } ou objets équivalents.
 * @param {string} s.strategy - Stratégie de positionnement ("absolute" | "fixed").
 * @param {Object} [t] - Options additionnelles.
 * @param {("clippingAncestors"|"viewport"|"document"|Element)} [t.boundary="clippingAncestors"] - Limite utilisée pour le calcul de clipping.
 * @param {("viewport"|"document")} [t.rootBoundary="viewport"] - Racine de référence pour le clipping.
 * @param {("reference"|"floating")} [t.elementContext="floating"] - Contexte d'élément dont on mesure le débordement.
 * @param {boolean} [t.altBoundary=false] - Si vrai, inverse la source de clipping (utilisé pour vérifier l'autre élément).
 * @param {number|Object} [t.padding=0] - Padding à appliquer autour de la zone de clipping (nombre unique ou inset {top,right,bottom,left}).
 * @returns {{top:number,bottom:number,left:number,right:number}} Distances (en pixels) entre les bords de l'élément/context et les bords de la zone de clipping, ajustées selon l'échelle de l'offsetParent.
 */
async function wt(s,t){var e;t===void 0&&(t={});let{x:i,y:n,platform:o,rects:r,elements:l,strategy:a}=s,{boundary:c="clippingAncestors",rootBoundary:h="viewport",elementContext:d="floating",altBoundary:p=!1,padding:f=0}=it(t,s),u=Ct(f),g=l[p?d==="floating"?"reference":"floating":d],w=U(await o.getClippingRect({element:(e=await(o.isElement==null?void 0:o.isElement(g)))==null||e?g:g.contextElement||await(o.getDocumentElement==null?void 0:o.getDocumentElement(l.floating)),boundary:c,rootBoundary:h,strategy:a})),b=d==="floating"?{x:i,y:n,width:r.floating.width,height:r.floating.height}:r.reference,v=await(o.getOffsetParent==null?void 0:o.getOffsetParent(l.floating)),L=await(o.isElement==null?void 0:o.isElement(v))?await(o.getScale==null?void 0:o.getScale(v))||{x:1,y:1}:{x:1,y:1},x=U(o.convertOffsetParentRelativeRectToViewportRelativeRect?await o.convertOffsetParentRelativeRectToViewportRelativeRect({elements:l,rect:b,offsetParent:v,strategy:a}):b);return{top:(w.top-x.top+u.top)/L.y,bottom:(x.bottom-w.bottom+u.bottom)/L.y,left:(w.left-x.left+u.left)/L.x,right:(x.right-w.right+u.right)/L.x}}var Rt=function(s){return s===void 0&&(s={}),{name:"flip",options:s,async fn(t){var e,i;let{placement:n,middlewareData:o,rects:r,initialPlacement:l,platform:a,elements:c}=t,{mainAxis:h=!0,crossAxis:d=!0,fallbackPlacements:p,fallbackStrategy:f="bestFit",fallbackAxisSideDirection:u="none",flipAlignment:m=!0,...g}=it(s,t);if((e=o.arrow)!=null&&e.alignmentOffset)return{};let w=z(n),b=M(l),v=z(l)===l,L=await(a.isRTL==null?void 0:a.isRTL(c.floating)),x=p||(v||!m?[Z(l)]:At(l)),J=u!=="none";!p&&J&&x.push(...St(l,m,u,L));let Q=[l,...x],W=await wt(t,g),F=[],I=((i=o.flip)==null?void 0:i.overflows)||[];if(h&&F.push(W[w]),d){let A=Ot(n,r,L);F.push(W[A[0]],W[A[1]])}if(I=[...I,{placement:n,overflows:F}],!F.every(A=>A<=0)){var j,q;let A=(((j=o.flip)==null?void 0:j.index)||0)+1,k=Q[A];if(k&&(!(d==="alignment"?b!==M(k):!1)||I.every(D=>M(D.placement)===b?D.overflows[0]>0:!0)))return{data:{index:A,overflows:I},reset:{placement:k}};let $=(q=I.filter(P=>P.overflows[0]<=0).sort((P,D)=>P.overflows[1]-D.overflows[1])[0])==null?void 0:q.placement;if(!$)switch(f){case"bestFit":{var X;let P=(X=I.filter(D=>{if(J){let V=M(D.placement);return V===b||V==="y"}return!0}).map(D=>[D.placement,D.overflows.filter(V=>V>0).reduce((V,Yt)=>V+Yt,0)]).sort((D,V)=>D[1]-V[1])[0])==null?void 0:X[0];P&&($=P);break}case"initialPlacement":$=l;break}if(n!==$)return{reset:{placement:$}}}return{}}}};var ne=new Set(["left","top"]);/**
 * Calcule un vecteur d'offset {x, y} adapté à un placement donné, en tenant compte de l'axe principal, de l'axe croisé, de l'alignement et du sens RTL.
 *
 * @param {Object} s - Contexte de positionnement.
 * @param {string} s.placement - Placement courant (par ex. "top-start", "right", ...).
 * @param {Object} s.platform - Interface de plateforme contenant au moins `isRTL` (peut être asynchrone).
 * @param {Object} s.elements - Éléments impliqués, doit contenir `floating`.
 * @param {number|Object} t - Déplacement demandé : soit un nombre (appliqué à l'axe principal), soit un objet { mainAxis, crossAxis, alignmentAxis }.
 * @param {number} [t.mainAxis] - Offset le long de l'axe principal.
 * @param {number} [t.crossAxis] - Offset le long de l'axe croisé.
 * @param {number|null} [t.alignmentAxis] - Offset appliqué à l'axe d'alignement (start/end) lorsque pertinent.
 * @returns {{x: number, y: number}} Objet contenant les décalages calculés pour x et y.
 */
async function oe(s,t){let{placement:e,platform:i,elements:n}=s,o=await(i.isRTL==null?void 0:i.isRTL(n.floating)),r=z(e),l=st(e),a=M(e)==="y",c=ne.has(r)?-1:1,h=o&&a?-1:1,d=it(t,s),{mainAxis:p,crossAxis:f,alignmentAxis:u}=typeof d=="number"?{mainAxis:d,crossAxis:0,alignmentAxis:null}:{mainAxis:d.mainAxis||0,crossAxis:d.crossAxis||0,alignmentAxis:d.alignmentAxis};return l&&typeof u=="number"&&(f=l==="end"?u*-1:u),a?{x:f*h,y:p*c}:{x:p*c,y:f*h}}var It=function(s){return s===void 0&&(s=0),{name:"offset",options:s,async fn(t){var e,i;let{x:n,y:o,placement:r,middlewareData:l}=t,a=await oe(t,s);return r===((e=l.offset)==null?void 0:e.placement)&&(i=l.arrow)!=null&&i.alignmentOffset?{}:{x:n+a.x,y:o+a.y,data:{...a,placement:r}}}}},kt=function(s){return s===void 0&&(s={}),{name:"shift",options:s,async fn(t){let{x:e,y:i,placement:n}=t,{mainAxis:o=!0,crossAxis:r=!1,limiter:l={fn:g=>{let{x:w,y:b}=g;return{x:w,y:b}}},...a}=it(s,t),c={x:e,y:i},h=await wt(t,a),d=M(z(n)),p=gt(d),f=c[p],u=c[d];if(o){let g=p==="y"?"top":"left",w=p==="y"?"bottom":"right",b=f+h[g],v=f-h[w];f=mt(b,f,v)}if(r){let g=d==="y"?"top":"left",w=d==="y"?"bottom":"right",b=u+h[g],v=u-h[w];u=mt(b,u,v)}let m=l.fn({...t,[p]:f,[d]:u});return{...m,data:{x:m.x-e,y:m.y-i,enabled:{[p]:o,[d]:r}}}}}};/**
 * Indique si l'objet global `window` est disponible.
 * @returns {boolean} `true` si l'objet global `window` est disponible, `false` sinon.
 */
function ct(){return typeof window<"u"}/**
 * Obtient le nom du nœud en minuscules pour un élément DOM ou renvoie "#document" pour un document.
 * @param {Node|Document} s - L'élément DOM ou l'objet document à interroger.
 * @returns {string} Le nom du nœud en minuscules (par exemple "div", "span"), ou "#document" si l'argument représente un document.
 */
function K(s){return Pt(s)?(s.nodeName||"").toLowerCase():"#document"}/**
 * Récupère l'objet Window associé au document propriétaire d'un nœud, ou le window global en secours.
 * @param {Node|Element|Document|null|undefined} s - Nœud dont on souhaite l'objet Window.
 * @returns {Window} L'objet Window lié au document du nœud, ou le window global si non disponible.
 */
function O(s){var t;return(s==null||(t=s.ownerDocument)==null?void 0:t.defaultView)||window}/**
 * Récupère l'élément racine (`documentElement`) du document associé à la cible fournie.
 * @param {Element|Document|Window} s - Élément, document ou objet window dont on veut le `documentElement`.
 * @returns {Element|undefined} L'élément `documentElement` du document associé, ou `undefined` s'il n'est pas disponible.
 */
function T(s){var t;return(t=(Pt(s)?s.ownerDocument:s.document)||window.document)==null?void 0:t.documentElement}/**
 * Détecte si la valeur fournie est un nœud DOM lorsque l'exécution se fait côté serveur (absence de `window`).
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si l'environnement est côté serveur et `s` est une instance de `Node` (ou `Node` du document propriétaire), `false` sinon.
 */
function Pt(s){return ct()?s instanceof Node||s instanceof O(s).Node:!1}/**
 * Détermine si la valeur fournie est un élément DOM.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` est une instance de `Element`, `false` sinon.
 */
function S(s){return ct()?s instanceof Element||s instanceof O(s).Element:!1}/**
 * Détermine si la valeur fournie est un élément DOM (HTMLElement) dans un environnement sans fenêtre.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` est une instance de `HTMLElement` (ou reconnue via le constructeur `HTMLElement` du document propriétaire) dans un environnement sans `window`, `false` sinon.
 */
function R(s){return ct()?s instanceof HTMLElement||s instanceof O(s).HTMLElement:!1}/**
 * Détermine si un nœud donné est une ShadowRoot (racine d'un shadow DOM).
 * @param {*} s - Le nœud à tester.
 * @returns {boolean} `true` si `s` est une instance de `ShadowRoot` (y compris via le `ShadowRoot` du document propriétaire), `false` sinon.
 */
function Tt(s){return!ct()||typeof ShadowRoot>"u"?!1:s instanceof ShadowRoot||s instanceof O(s).ShadowRoot}var re=new Set(["inline","contents"]);/**
 * Détermine si un élément crée un conteneur d'overflow (scrollable, clipped ou overlay).
 *
 * @param {Element} s - Élément DOM à tester.
 * @returns {boolean} `true` si l'élément a une valeur d'overflow parmi `auto`, `scroll`, `overlay`, `hidden` ou `clip` sur l'une des directions et que son `display` n'est pas `inline` ni `contents`, `false` sinon.
 */
function Y(s){let{overflow:t,overflowX:e,overflowY:i,display:n}=C(s);return/auto|scroll|overlay|hidden|clip/.test(t+i+e)&&!re.has(n)}var le=new Set(["table","td","th"]);/**
 * Détermine si un nœud correspond à un élément de tableau ("table", "td" ou "th").
 * @param {Element|Node|string} s - Élément DOM, nœud ou nom de balise à tester.
 * @returns {boolean} `true` si la balise est `table`, `td` ou `th`, `false` sinon.
 */
function Mt(s){return le.has(K(s))}var ae=[":popover-open",":modal"];/**
 * Vérifie si un élément correspond à l'un des pseudo-sélecteurs prédéfinis.
 * @param {Element} s - Élément DOM à tester.
 * @returns {boolean} `true` si l'élément correspond à au moins un sélecteur de `ae`, `false` sinon.
 */
function nt(s){return ae.some(t=>{try{return s.matches(t)}catch{return!1}})}var ce=["transform","translate","scale","rotate","perspective"],he=["transform","translate","scale","rotate","perspective","filter"],de=["paint","layout","strict","content"];/**
 * Détecte si un élément (ou un objet de style) applique des propriétés visuelles qui créent un nouveau contexte de rendu (transform, filter, backdrop-filter, contain, will-change, etc.).
 *
 * @param {Element|CSSStyleDeclaration|Object} s - Élément DOM ou objet de style/computedStyle à tester. Si un Element est fourni, ses styles calculés sont utilisés.
 * @returns {boolean} `true` si l'élément/les styles contiennent au moins une des propriétés indiquant un contexte visuel spécial, `false` sinon.
 */
function ht(s){let t=dt(),e=S(s)?C(s):s;return ce.some(i=>e[i]?e[i]!=="none":!1)||(e.containerType?e.containerType!=="normal":!1)||!t&&(e.backdropFilter?e.backdropFilter!=="none":!1)||!t&&(e.filter?e.filter!=="none":!1)||he.some(i=>(e.willChange||"").includes(i))||de.some(i=>(e.contain||"").includes(i))}/**
 * Trouve l'ancêtre le plus proche d'un nœud pour lequel la condition de style `ht` s'applique.
 *
 * Parcourt la chaîne des parents (en commençant par le parent direct) et renvoie le premier élément pour lequel `ht` retourne une valeur vraie. Si un élément correspond à la condition détectée par `nt` (pseudo-sélecteur d'arrêt), la recherche s'arrête et `null` est renvoyé.
 *
 * @param {Node|Element} s - Le nœud de départ dont on recherche l'ancêtre.
 * @returns {Element|null} L'ancêtre trouvé, ou `null` si aucun ancêtre correspondant n'est trouvé ou si un pseudo-sélecteur d'arrêt est rencontré.
 */
function Bt(s){let t=B(s);for(;R(t)&&!_(t);){if(ht(t))return t;if(nt(t))return null;t=B(t)}return null}/**
 * Détermine si le navigateur prend en charge le filtre d'arrière-plan CSS (`backdrop-filter`).
 * @returns {boolean} `true` si le navigateur supporte `backdrop-filter` ou son équivalent préfixé `-webkit-backdrop-filter`, `false` sinon.
 */
function dt(){return typeof CSS>"u"||!CSS.supports?!1:CSS.supports("-webkit-backdrop-filter","none")}var fe=new Set(["html","body","#document"]);/**
 * Détermine si un nœud correspond à l'un des éléments racine du document (html, body ou #document).
 * @param {Node|Element|null|undefined} s - Le nœud à tester.
 * @returns {boolean} `true` si le nœud est `html`, `body` ou `#document`, `false` sinon.
 */
function _(s){return fe.has(K(s))}/**
 * Récupère l'objet CSSStyleDeclaration calculé pour un élément DOM.
 * @param {Element} s - Élément DOM dont on souhaite obtenir le style calculé.
 * @returns {CSSStyleDeclaration} L'objet `CSSStyleDeclaration` représentant les styles calculés.
 */
function C(s){return O(s).getComputedStyle(s)}/**
 * Récupère les valeurs de défilement horizontal et vertical pour un élément ou la fenêtre.
 * @param {Element|Window} s - L'élément DOM ou l'objet global window dont on souhaite lire le défilement.
 * @returns {{scrollLeft: number, scrollTop: number}} Un objet contenant les valeurs `scrollLeft` et `scrollTop`.
 */
function ot(s){return S(s)?{scrollLeft:s.scrollLeft,scrollTop:s.scrollTop}:{scrollLeft:s.scrollX,scrollTop:s.scrollY}}/**
 * Récupère l'ancêtre logique d'un nœud en résolvant les slots assignés et les hôtes shadow DOM.
 * @param {Node} s - Le nœud cible.
 * @returns {Node} L'ancêtre logique : le slot assigné, le parent DOM, l'hôte du shadow DOM ou l'élément racine du document.
 */
function B(s){if(K(s)==="html")return s;let t=s.assignedSlot||s.parentNode||Tt(s)&&s.host||T(s);return Tt(t)?t.host:t}/**
 * Résout l'ancêtre racine ou conteneur pertinent pour un nœud, en gérant les hosts Shadow DOM et les cas particuliers du document.
 * @param {Node|Element} s - Le nœud dont on recherche la racine/conteneru.
 * @returns {Element|Document|null} L'élément racine ou le body du document lorsque pertinent, ou `null` si aucun conteneur approprié n'est trouvé.
 */
function Nt(s){let t=B(s);return _(t)?s.ownerDocument?s.ownerDocument.body:s.body:R(t)&&Y(t)?t:Nt(t)}/**
 * Construit récursivement la liste des ancêtres et conteneurs pertinents pour le calcul des offsets d'un nœud.
 * La liste contient notamment l'élément racine, le visualViewport si présent, les conteneurs de défilement pertinents et, si demandé, les éléments d'encapsulation (frames).
 * @param {Node|Element} s - Le nœud dont on collecte les conteneurs.
 * @param {Array} [t=[]] - Accumulateur pour les conteneurs (usage interne ; peut être omis).
 * @param {boolean} [e=true] - Indique si les éléments d'encapsulation (frames) doivent être inclus.
 * @returns {Array} Un tableau ordonné des conteneurs/ancêtres pertinents pour le calcul des offsets. */
function at(s,t,e){var i;t===void 0&&(t=[]),e===void 0&&(e=!0);let n=Nt(s),o=n===((i=s.ownerDocument)==null?void 0:i.body),r=O(n);if(o){let l=ft(r);return t.concat(r,r.visualViewport||[],Y(n)?n:[],l&&e?at(l):[])}return t.concat(n,at(n,[],e))}/**
 * Récupère l'élément frame (frameElement) associé à un nœud DOM lorsque son parent est présent.
 * @param {Node|Element} s - Le nœud DOM cible.
 * @returns {Element|null} `s.frameElement` si le nœud a un parent avec prototype, `null` sinon.
 */
function ft(s){return s.parent&&Object.getPrototypeOf(s.parent)?s.frameElement:null}/**
 * Détermine la largeur et la hauteur effectives d'un élément et signale si les valeurs calculées ont été remplacées par ses dimensions d'offset.
 * @param {Element} s - Élément DOM dont on veut la taille.
 * @returns {{width: number, height: number, $: boolean}} Objet contenant `width` et `height` normalisés, et `$` valant `true` si les dimensions initialement extraites ont été remplacées par `offsetWidth`/`offsetHeight`.
 */
function zt(s){let t=C(s),e=parseFloat(t.width)||0,i=parseFloat(t.height)||0,n=R(s),o=n?s.offsetWidth:e,r=n?s.offsetHeight:i,l=et(e)!==o||et(i)!==r;return l&&(e=o,i=r),{width:e,height:i,$:l}}/**
 * Renvoie l'élément DOM s'il s'agit d'un élément, sinon renvoie sa propriété `contextElement`.
 * @param {*} s - Un élément DOM ou un objet possédant une propriété `contextElement` pointant vers un élément DOM.
 * @returns {Element|undefined} L'élément DOM trouvé, ou `undefined` si aucun élément n'est disponible.
 */
function Wt(s){return S(s)?s:s.contextElement}/**
 * Calcule les facteurs d'échelle horizontaux et verticaux d'un élément par rapport à son bounding rect.
 *
 * Calcule les rapports entre les dimensions renvoyées par getBoundingClientRect() et les dimensions d'élément (width/height),
 * en tenant compte d'un ajustement arrondi lorsque nécessaire. Si l'élément n'est pas trouvé ou si un ratio est invalide,
 * la valeur par défaut 1 est utilisée.
 *
 * @param {Element|Object} s - L'élément DOM (ou un objet assimilable) dont on souhaite obtenir l'échelle.
 * @returns {{x: number, y: number}} Les facteurs d'échelle : `x` pour l'axe horizontal, `y` pour l'axe vertical.
 */
function G(s){let t=Wt(s);if(!R(t))return E(1);let e=t.getBoundingClientRect(),{width:i,height:n,$:o}=zt(t),r=(o?et(e.width):e.width)/i,l=(o?et(e.height):e.height)/n;return(!r||!Number.isFinite(r))&&(r=1),(!l||!Number.isFinite(l))&&(l=1),{x:r,y:l}}var pe=E(0);/**
 * Renvoie le décalage du visualViewport associé à l'élément fourni si disponible, sinon un vecteur nul.
 * @param {Element|Document|Window} s - Élément, document ou fenêtre utilisé pour déterminer la fenêtre associée.
 * @returns {{x:number,y:number}} Le décalage en pixels du visualViewport : `x` correspond à `offsetLeft`, `y` à `offsetTop`. Si le visualViewport n'est pas pris en charge, retourne `{x:0,y:0}`.
 */
function $t(s){let t=O(s);return!dt()||!t.visualViewport?pe:{x:t.visualViewport.offsetLeft,y:t.visualViewport.offsetTop}}/**
 * Valide le drapeau d'activation `t` en fonction de la présence et de la correspondance de `e` avec le contexte de `s`.
 *
 * @param {*} s - Élément ou valeur de référence utilisée pour déterminer le contexte de comparaison.
 * @param {boolean} [t=false] - Drapeau à valider.
 * @param {*} e - Contexte ou cible de comparaison.
 * @returns {boolean} `true` si `t` est vrai, `e` est fourni et `e` correspond au contexte associé à `s`, `false` sinon.
 */
function ue(s,t,e){return t===void 0&&(t=!1),!e||t&&e!==O(s)?!1:t}/**
 * Calcule le rectangle positionné (x, y, width, height) d'un élément en tenant compte des parents de décalage, du redimensionnement (scale) et des décalages de la visualViewport.
 *
 * @param {Element|Text|HTMLElement} s - Élément pour lequel calculer le rectangle.
 * @param {boolean} [t=false] - Si true, applique la mise à l'échelle (scale) détectée sur l'élément ou sur l'élément `i` fourni.
 * @param {boolean} [e=false] - Si true, inclut le décalage de la visualViewport dans le calcul.
 * @param {Element|HTMLElement|null} [i] - Parent de référence optionnel utilisé pour le calcul du scale/offset ; si absent, l'élément `s` est utilisé.
 * @returns {{x:number,y:number,width:number,height:number}} Objet rect standardisé avec les coordonnées et dimensions calculées.
 */
function rt(s,t,e,i){t===void 0&&(t=!1),e===void 0&&(e=!1);let n=s.getBoundingClientRect(),o=Wt(s),r=E(1);t&&(i?S(i)&&(r=G(i)):r=G(s));let l=ue(o,e,i)?$t(o):E(0),a=(n.left+l.x)/r.x,c=(n.top+l.y)/r.y,h=n.width/r.x,d=n.height/r.y;if(o){let p=O(o),f=i&&S(i)?O(i):i,u=p,m=ft(u);for(;m&&i&&f!==u;){let g=G(m),w=m.getBoundingClientRect(),b=C(m),v=w.left+(m.clientLeft+parseFloat(b.paddingLeft))*g.x,L=w.top+(m.clientTop+parseFloat(b.paddingTop))*g.y;a*=g.x,c*=g.y,h*=g.x,d*=g.y,a+=v,c+=L,u=O(m),m=ft(u)}}return U({width:h,height:d,x:a,y:c})}/**
 * Calcule la coordonnée x (gauche) en combinant le décalage de défilement et un offset.
 * @param {Element|Document|Window} s - Élément, document ou fenêtre utilisé pour lire le scroll horizontal.
 * @param {{left:number}|null} [t] - Objet optionnel contenant la propriété `left` à utiliser comme offset; si absent, le `left` du rect de `T(s)` est utilisé.
 * @returns {number} La coordonnée x (gauche) en pixels, additionnée du `scrollLeft` courant.
 */
function pt(s,t){let e=ot(s).scrollLeft;return t?t.left+e:rt(T(s)).left+e}/**
 * Calcule les coordonnées absolues {x, y} d'un élément DOM en tenant compte du défilement du conteneur fourni.
 * @param {Element} s - Élément DOM dont on veut la position.
 * @param {{scrollLeft:number,scrollTop:number}} t - Conteneur de référence fournissant les valeurs de défilement.
 * @returns {{x:number,y:number}} Objet contenant les coordonnées en pixels `{x, y}`.
 */
function Ut(s,t){let e=s.getBoundingClientRect(),i=e.left+t.scrollLeft-pt(s,e),n=e.top+t.scrollTop;return{x:i,y:n}}/**
 * Calcule le rectangle d'un élément en coordonnées relatives à la fenêtre d'affichage (viewport),
 * en tenant compte du parent d'offset, de la stratégie de positionnement et des éventuelles mises à l'échelle.
 * @param {Object} s - Paramètres d'entrée.
 * @param {Object} [s.elements] - Objet contenant les éléments concernés (ex. { floating, reference }).
 * @param {Object} s.rect - Rectangle source relatif à l'offset parent ({ width, height, x, y }).
 * @param {Element|Document} s.offsetParent - L'offsetParent utilisé pour le calcul.
 * @param {string} s.strategy - Stratégie de positionnement ("fixed" ou autre) influant sur le calcul des décalages.
 * @returns {Object} Un rectangle normalisé avec les propriétés `width`, `height`, `x` et `y` exprimées en coordonnées viewport.
 */
function me(s){let{elements:t,rect:e,offsetParent:i,strategy:n}=s,o=n==="fixed",r=T(i),l=t?nt(t.floating):!1;if(i===r||l&&o)return e;let a={scrollLeft:0,scrollTop:0},c=E(1),h=E(0),d=R(i);if((d||!d&&!o)&&((K(i)!=="body"||Y(r))&&(a=ot(i)),R(i))){let f=rt(i);c=G(i),h.x=f.x+i.clientLeft,h.y=f.y+i.clientTop}let p=r&&!d&&!o?Ut(r,a):E(0);return{width:e.width*c.x,height:e.height*c.y,x:e.x*c.x-a.scrollLeft*c.x+h.x+p.x,y:e.y*c.y-a.scrollTop*c.y+h.y+p.y}}/**
 * Récupère tous les ClientRects fournis par getClientRects sous forme de tableau.
 * @param {Element|Range|ClientRectList|DOMRectList} s - Élément ou objet prenant en charge `getClientRects`.
 * @returns {Array<ClientRect|DOMRect>} Un tableau des rectangles de client (ClientRect/DOMRect).
 */
function ge(s){return Array.from(s.getClientRects())}/**
 * Calcule le rectangle de clipping (largeur, hauteur et position) pour le document ou le body associé à l'élément fourni.
 * @param {Element|Document} s - Élément ou document servant de référence pour le calcul.
 * @returns {{width: number, height: number, x: number, y: number}} Le rectangle de clipping : `width` et `height` sont les dimensions disponibles, `x` et `y` sont les coordonnées du coin supérieur gauche. 
 */
function be(s){let t=T(s),e=ot(s),i=s.ownerDocument.body,n=H(t.scrollWidth,t.clientWidth,i.scrollWidth,i.clientWidth),o=H(t.scrollHeight,t.clientHeight,i.scrollHeight,i.clientHeight),r=-e.scrollLeft+pt(s),l=-e.scrollTop;return C(i).direction==="rtl"&&(r+=H(t.clientWidth,i.clientWidth)-n),{width:n,height:o,x:r,y:l}}var Ft=25;/**
 * Calcule les dimensions et l'offset d'affichage (viewport) pour un document ou un élément donné.
 *
 * @param {Element|Document} s - Élément ou document de référence utilisé pour déterminer le viewport.
 * @param {string} [t] - Stratégie de positionnement (par ex. `"fixed"`), influence l'utilisation de visualViewport.
 * @returns {{width: number, height: number, x: number, y: number}} La largeur et la hauteur du viewport en pixels et les offsets x/y (généralement 0 sauf si visualViewport est disponible et utilisée).
 */
function ye(s,t){let e=O(s),i=T(s),n=e.visualViewport,o=i.clientWidth,r=i.clientHeight,l=0,a=0;if(n){o=n.width,r=n.height;let h=dt();(!h||h&&t==="fixed")&&(l=n.offsetLeft,a=n.offsetTop)}let c=pt(i);if(c<=0){let h=i.ownerDocument,d=h.body,p=getComputedStyle(d),f=h.compatMode==="CSS1Compat"&&parseFloat(p.marginLeft)+parseFloat(p.marginRight)||0,u=Math.abs(i.clientWidth-d.clientWidth-f);u<=Ft&&(o-=u)}else c<=Ft&&(o+=c);return{width:o,height:r,x:l,y:a}}var we=new Set(["absolute","fixed"]);/**
 * Calcule le rectangle de l'élément en tenant compte des bordures client et de l'échelle du conteneur.
 *
 * @param {Element} s - Élément DOM dont on calcule le rectangle.
 * @param {string} t - Stratégie de positionnement ; si la valeur est `"fixed"`, le calcul se fait par rapport à la fenêtre.
 * @returns {{width: number, height: number, x: number, y: number}} Objet contenant la largeur et la hauteur (en pixels) et les coordonnées x/y ajustées pour l'échelle et les bordures client.
 */
function xe(s,t){let e=rt(s,!0,t==="fixed"),i=e.top+s.clientTop,n=e.left+s.clientLeft,o=R(s)?G(s):E(1),r=s.clientWidth*o.x,l=s.clientHeight*o.y,a=n*o.x,c=i*o.y;return{width:r,height:l,x:a,y:c}}/**
 * Calcule le rectangle de découpe (clipping) ou le rectangle relatif correspondant à la frontière fournie.
 *
 * @param {Element} s - L'élément de référence utilisé pour convertir les coordonnées si nécessaire.
 * @param {"viewport"|"document"|Element|Object} t - La frontière cible :
 *   - "viewport" pour la fenêtre d'affichage,
 *   - "document" pour le document entier,
 *   - un Element pour utiliser cet élément comme frontière,
 *   - ou un objet rect (x,y,width,height) représentant une frontière personnalisée.
 * @param {any} e - Option passée aux helpers de calcul de taille/position (propagée aux fonctions internes).
 * @returns {Object} Un rectangle standardisé contenant au minimum `{ x, y, width, height, top, left, right, bottom }`.
 */
function Vt(s,t,e){let i;if(t==="viewport")i=ye(s,e);else if(t==="document")i=be(T(s));else if(S(t))i=xe(t,e);else{let n=$t(s);i={x:t.x-n.x,y:t.y-n.y,width:t.width,height:t.height}}return U(i)}/**
 * Détecte si, en remontant depuis l'élément fourni, un ancêtre entre cet élément et la cible possède la propriété CSS `position: fixed`.
 *
 * Parcourt la chaîne de parents/hôtes à partir du parent de `s` jusqu'à `t` (ou jusqu'à la racine du document) et renvoie `true` dès qu'un ancêtre a `position: fixed`.
 *
 * @param {Node} s - Élément de départ dont on commence la remontée par le parent.
 * @param {Node} t - Élément cible où l'arrêt de la recherche doit se produire (si rencontré, la recherche renvoie `false`).
 * @returns {boolean} `true` si un ancêtre rencontré avant `t` a `position: fixed`, `false` sinon.
 */
function Kt(s,t){let e=B(s);return e===t||!S(e)||_(e)?!1:C(e).position==="fixed"||Kt(e,t)}/**
 * Détermine et met en cache la liste des parents d'offset applicables pour un élément donné.
 *
 * Parcourt la chaîne de parents visuels/DOM pour produire un tableau d'éléments pouvant servir d'offsetParent,
 * en excluant le body et en tenant compte des positions CSS (fixed/static), des conteneurs de défilement/clip et
 * d'autres contraintes de rendu. Le résultat est mis en cache dans la Map fournie.
 *
 * @param {Element} s - Élément source dont on recherche les parents d'offset.
 * @param {Map<Element, Element[]>} t - Map de cache où la clé est l'élément source et la valeur est le tableau d'offsetParents calculé.
 * @returns {Element[]} Tableau d'éléments candidats pour être utilisés comme offsetParent, dans l'ordre de proximité.
 */
function ve(s,t){let e=t.get(s);if(e)return e;let i=at(s,[],!1).filter(l=>S(l)&&K(l)!=="body"),n=null,o=C(s).position==="fixed",r=o?B(s):s;for(;S(r)&&!_(r);){let l=C(r),a=ht(r);!a&&l.position==="fixed"&&(n=null),(o?!a&&!n:!a&&l.position==="static"&&!!n&&we.has(n.position)||Y(r)&&!a&&Kt(s,r))?i=i.filter(h=>h!==r):n=l,r=B(r)}return t.set(s,i),i}/**
 * Calcule le rectangle de découpe (clipping) final pour un élément en tenant compte de la frontière demandée et de la stratégie de positionnement.
 *
 * @param {Object} s - Paramètres.
 * @param {Element} s.element - Élément DOM pour lequel calculer le rectangle de découpe.
 * @param {'viewport'|'document'|'clippingAncestors'|Element} s.boundary - Bordure à utiliser : viewport, document, une liste d'ancêtres de découpe ('clippingAncestors') ou un élément spécifique.
 * @param {'viewport'|'document'|Element} s.rootBoundary - Bordure racine de repli si applicable.
 * @param {'absolute'|'fixed'} s.strategy - Stratégie de positionnement utilisée pour les calculs.
 * @returns {{width: number, height: number, x: number, y: number}} Objet rectangulaire contenant la largeur, la hauteur et les coordonnées x/y du coin supérieur gauche du rectangle de découpe.
 */
function Le(s){let{element:t,boundary:e,rootBoundary:i,strategy:n}=s,r=[...e==="clippingAncestors"?nt(t)?[]:ve(t,this._c):[].concat(e),i],l=r[0],a=r.reduce((c,h)=>{let d=Vt(t,h,n);return c.top=H(d.top,c.top),c.right=tt(d.right,c.right),c.bottom=tt(d.bottom,c.bottom),c.left=H(d.left,c.left),c},Vt(t,l,n));return{width:a.right-a.left,height:a.bottom-a.top,x:a.left,y:a.top}}/**
 * Obtient la largeur et la hauteur d'un élément ou d'un rectangle.
 * @param {Element|ClientRect|DOMRect|Object} s - Élément DOM ou objet rectangulaire compatible (possède des dimensions mesurables).
 * @return {{width:number,height:number}} Objet contenant `width` et `height` en pixels.
 */
function Oe(s){let{width:t,height:e}=zt(s);return{width:t,height:e}}/**
 * Calcule le rectangle final (position et taille) d'un élément flottant par rapport à son contexte d'offset.
 *
 * @param {Element|Object} s - L'élément flottant ou son rect-like objet source.
 * @param {Element} t - L'offset parent utilisé pour le calcul (peut être le document/body).
 * @param {'fixed'|'absolute'|string} e - Stratégie de positionnement; utilise la valeur `'fixed'` pour une positionnement relatif à la fenêtre.
 * @return {{x:number,y:number,width:number,height:number}} Un objet contenant les coordonnées finales `x` et `y` (en pixels) et la `width`/`height` du rectangle calculé.
 */
function Ae(s,t,e){let i=R(t),n=T(t),o=e==="fixed",r=rt(s,!0,o,t),l={scrollLeft:0,scrollTop:0},a=E(0);function c(){a.x=pt(n)}if(i||!i&&!o)if((K(t)!=="body"||Y(n))&&(l=ot(t)),i){let f=rt(t,!0,o,t);a.x=f.x+t.clientLeft,a.y=f.y+t.clientTop}else n&&c();o&&!i&&n&&c();let h=n&&!i&&!o?Ut(n,l):E(0),d=r.left+l.scrollLeft-a.x-h.x,p=r.top+l.scrollTop-a.y-h.y;return{x:d,y:p,width:r.width,height:r.height}}/**
 * Détermine si l'élément a une position CSS "static".
 * @param {Element} s - Élément DOM à tester.
 * @returns {boolean} `true` si la propriété CSS `position` calculée vaut `"static"`, `false` sinon.
 */
function xt(s){return C(s).position==="static"}/**
 * Détermine l'offset parent utilisable pour un élément DOM.
 *
 * Si l'élément n'est pas un élément valide ou a une position CSS `fixed`, renvoie `null`.
 * Si un second argument `t` est fourni, il est appelé avec l'élément et sa valeur est retournée.
 * Sinon retourne `element.offsetParent`, en remplaçant le documentElement par `document.body` lorsque nécessaire.
 *
 * @param {Element|Node} s - L'élément DOM dont on recherche l'offset parent.
 * @param {(el: Element|Node) => Element|null} [t] - Fonction optionnelle personnalisée pour calculer l'offset parent.
 * @returns {Element|null} L'offset parent déterminé, ou `null` si aucun offset parent applicable.
 */
function Ht(s,t){if(!R(s)||C(s).position==="fixed")return null;if(t)return t(s);let e=s.offsetParent;return T(s)===e&&(e=e.ownerDocument.body),e}/**
 * Détermine l'élément offsetParent pertinent pour un nœud donné en appliquant les règles DOM spécifiques
 * (gestion des shadow DOM, des éléments statiques, des éléments de table et des conteneurs visuels).
 *
 * @param {Element|Node} s - Le nœud pour lequel rechercher l'offsetParent.
 * @param {?any} [t] - Contexte optionnel utilisé pour la résolution (p. ex. document ou window); peut être omis.
 * @returns {Element} L'élément offsetParent à utiliser pour le positionnement ; si aucun parent approprié n'est trouvé,
 * retourne l'élément de document/viewport approprié.
 */
function _t(s,t){let e=O(s);if(nt(s))return e;if(!R(s)){let n=B(s);for(;n&&!_(n);){if(S(n)&&!xt(n))return n;n=B(n)}return e}let i=Ht(s,t);for(;i&&Mt(i)&&xt(i);)i=Ht(i,t);return i&&_(i)&&xt(i)&&!ht(i)?e:i||Bt(s)||e}var Se=async function(s){let t=this.getOffsetParent||_t,e=this.getDimensions,i=await e(s.floating);return{reference:Ae(s.reference,await t(s.floating),s.strategy),floating:{x:0,y:0,width:i.width,height:i.height}}};/**
 * Indique si un élément utilise la direction d'écriture de droite à gauche (RTL).
 * @param {Element} s - Élément DOM dont on souhaite vérifier la direction.
 * @returns {boolean} `true` si la propriété CSS `direction` de l'élément est `"rtl"`, `false` sinon.
 */
function Ce(s){return C(s).direction==="rtl"}var De={convertOffsetParentRelativeRectToViewportRelativeRect:me,getDocumentElement:T,getClippingRect:Le,getOffsetParent:_t,getElementRects:Se,getClientRects:ge,getDimensions:Oe,getScale:G,isElement:S,isRTL:Ce};var Jt=It;var jt=kt,qt=Rt;var Xt=(s,t,e)=>{let i=new Map,n={platform:De,...e},o={...n.platform,_c:i};return Et(s,t,{...n,platform:o})};/**
 * Détecte si une valeur est nulle, une chaîne vide ou une chaîne composée uniquement d'espaces.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` est `null`, `undefined`, une chaîne vide `""` ou une chaîne ne contenant que des espaces, `false` sinon.
 */
function N(s){return s==null||s===""||typeof s=="string"&&s.trim()===""}/**
 * Vérifie qu'une valeur n'est ni `null` ni une chaîne vide.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` n'est pas `null` et n'est pas une chaîne vide, `false` sinon.
 */
function y(s){return!N(s)}var ut=class{constructor({element:t,options:e,placeholder:i,state:n,canOptionLabelsWrap:o=!0,canSelectPlaceholder:r=!0,initialOptionLabel:l=null,initialOptionLabels:a=null,initialState:c=null,isHtmlAllowed:h=!1,isAutofocused:d=!1,isDisabled:p=!1,isMultiple:f=!1,isSearchable:u=!1,getOptionLabelUsing:m=null,getOptionLabelsUsing:g=null,getOptionsUsing:w=null,getSearchResultsUsing:b=null,hasDynamicOptions:v=!1,hasDynamicSearchResults:L=!0,searchPrompt:x="Search...",searchDebounce:J=1e3,loadingMessage:Q="Loading...",searchingMessage:W="Searching...",noSearchResultsMessage:F="No results found",maxItems:I=null,maxItemsMessage:j="Maximum number of items selected",optionsLimit:q=null,position:X=null,searchableOptionFields:A=["label"],livewireId:k=null,statePath:$=null,onStateChange:P=()=>{}}){this.element=t,this.options=e,this.originalOptions=JSON.parse(JSON.stringify(e)),this.placeholder=i,this.state=n,this.canOptionLabelsWrap=o,this.canSelectPlaceholder=r,this.initialOptionLabel=l,this.initialOptionLabels=a,this.initialState=c,this.isHtmlAllowed=h,this.isAutofocused=d,this.isDisabled=p,this.isMultiple=f,this.isSearchable=u,this.getOptionLabelUsing=m,this.getOptionLabelsUsing=g,this.getOptionsUsing=w,this.getSearchResultsUsing=b,this.hasDynamicOptions=v,this.hasDynamicSearchResults=L,this.searchPrompt=x,this.searchDebounce=J,this.loadingMessage=Q,this.searchingMessage=W,this.noSearchResultsMessage=F,this.maxItems=I,this.maxItemsMessage=j,this.optionsLimit=q,this.position=X,this.searchableOptionFields=Array.isArray(A)?A:["label"],this.livewireId=k,this.statePath=$,this.onStateChange=P,this.labelRepository={},this.isOpen=!1,this.selectedIndex=-1,this.searchQuery="",this.searchTimeout=null,this.isSearching=!1,this.selectedDisplayVersion=0,this.render(),this.setUpEventListeners(),this.isAutofocused&&this.selectButton.focus()}populateLabelRepositoryFromOptions(t){if(!(!t||!Array.isArray(t)))for(let e of t)e.options&&Array.isArray(e.options)?this.populateLabelRepositoryFromOptions(e.options):e.value!==void 0&&e.label!==void 0&&(this.labelRepository[e.value]=e.label)}render(){this.populateLabelRepositoryFromOptions(this.options),this.container=document.createElement("div"),this.container.className="fi-select-input-ctn",this.canOptionLabelsWrap||this.container.classList.add("fi-select-input-ctn-option-labels-not-wrapped"),this.container.setAttribute("aria-haspopup","listbox"),this.selectButton=document.createElement("button"),this.selectButton.className="fi-select-input-btn",this.selectButton.type="button",this.selectButton.setAttribute("aria-expanded","false"),this.selectedDisplay=document.createElement("div"),this.selectedDisplay.className="fi-select-input-value-ctn",this.updateSelectedDisplay(),this.selectButton.appendChild(this.selectedDisplay),this.dropdown=document.createElement("div"),this.dropdown.className="fi-dropdown-panel fi-scrollable",this.dropdown.setAttribute("role","listbox"),this.dropdown.setAttribute("tabindex","-1"),this.dropdown.style.display="none",this.dropdownId=`fi-select-input-dropdown-${Math.random().toString(36).substring(2,11)}`,this.dropdown.id=this.dropdownId,this.isMultiple&&this.dropdown.setAttribute("aria-multiselectable","true"),this.isSearchable&&(this.searchContainer=document.createElement("div"),this.searchContainer.className="fi-select-input-search-ctn",this.searchInput=document.createElement("input"),this.searchInput.className="fi-input",this.searchInput.type="text",this.searchInput.placeholder=this.searchPrompt,this.searchInput.setAttribute("aria-label","Search"),this.searchContainer.appendChild(this.searchInput),this.dropdown.appendChild(this.searchContainer),this.searchInput.addEventListener("input",t=>{this.isDisabled||this.handleSearch(t)}),this.searchInput.addEventListener("keydown",t=>{if(!this.isDisabled){if(t.key==="Tab"){t.preventDefault();let e=this.getVisibleOptions();if(e.length===0)return;t.shiftKey?this.selectedIndex=e.length-1:this.selectedIndex=0,e.forEach(i=>{i.classList.remove("fi-selected")}),e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus()}else if(t.key==="ArrowDown"){if(t.preventDefault(),t.stopPropagation(),this.getVisibleOptions().length===0)return;this.selectedIndex=-1,this.searchInput.blur(),this.focusNextOption()}else if(t.key==="ArrowUp"){t.preventDefault(),t.stopPropagation();let e=this.getVisibleOptions();if(e.length===0)return;this.selectedIndex=e.length-1,this.searchInput.blur(),e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus(),e[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",e[this.selectedIndex].id),this.scrollOptionIntoView(e[this.selectedIndex])}else if(t.key==="Enter"){if(t.preventDefault(),t.stopPropagation(),this.isSearching)return;let e=this.getVisibleOptions();if(e.length===0)return;let i=e.find(o=>{let r=o.getAttribute("aria-disabled")==="true",l=o.classList.contains("fi-disabled"),a=o.offsetParent===null;return!(r||l||a)});if(!i)return;let n=i.getAttribute("data-value");if(n===null)return;this.selectOption(n)}}})),this.optionsList=document.createElement("ul"),this.renderOptions(),this.container.appendChild(this.selectButton),this.container.appendChild(this.dropdown),this.element.appendChild(this.container),this.applyDisabledState()}renderOptions(){this.optionsList.innerHTML="";let t=0,e=this.options,i=0,n=!1;this.options.forEach(l=>{l.options&&Array.isArray(l.options)?(i+=l.options.length,n=!0):i++}),n?this.optionsList.className="fi-select-input-options-ctn":i>0&&(this.optionsList.className="fi-dropdown-list");let o=n?null:this.optionsList,r=0;for(let l of e){if(this.optionsLimit&&r>=this.optionsLimit)break;if(l.options&&Array.isArray(l.options)){let a=l.options;if(this.isMultiple&&Array.isArray(this.state)&&this.state.length>0&&(a=l.options.filter(c=>!this.state.includes(c.value))),a.length>0){if(this.optionsLimit){let c=this.optionsLimit-r;c<a.length&&(a=a.slice(0,c))}this.renderOptionGroup(l.label,a),r+=a.length,t+=a.length}}else{if(this.isMultiple&&Array.isArray(this.state)&&this.state.includes(l.value))continue;!o&&n&&(o=document.createElement("ul"),o.className="fi-dropdown-list",this.optionsList.appendChild(o));let a=this.createOptionElement(l.value,l);o.appendChild(a),r++,t++}}t===0?(this.searchQuery?this.showNoResultsMessage():this.isMultiple&&this.isOpen&&!this.isSearchable&&this.closeDropdown(),this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList)):(this.hideLoadingState(),this.optionsList.parentNode!==this.dropdown&&this.dropdown.appendChild(this.optionsList))}renderOptionGroup(t,e){if(e.length===0)return;let i=document.createElement("li");i.className="fi-select-input-option-group";let n=document.createElement("div");n.className="fi-dropdown-header",n.textContent=t;let o=document.createElement("ul");o.className="fi-dropdown-list",e.forEach(r=>{let l=this.createOptionElement(r.value,r);o.appendChild(l)}),i.appendChild(n),i.appendChild(o),this.optionsList.appendChild(i)}createOptionElement(t,e){let i=t,n=e,o=!1;typeof e=="object"&&e!==null&&"label"in e&&"value"in e&&(i=e.value,n=e.label,o=e.isDisabled||!1);let r=document.createElement("li");r.className="fi-dropdown-list-item fi-select-input-option",o&&r.classList.add("fi-disabled");let l=`fi-select-input-option-${Math.random().toString(36).substring(2,11)}`;if(r.id=l,r.setAttribute("role","option"),r.setAttribute("data-value",i),r.setAttribute("tabindex","0"),o&&r.setAttribute("aria-disabled","true"),this.isHtmlAllowed&&typeof n=="string"){let h=document.createElement("div");h.innerHTML=n;let d=h.textContent||h.innerText||n;r.setAttribute("aria-label",d)}let a=this.isMultiple?Array.isArray(this.state)&&this.state.includes(i):this.state===i;r.setAttribute("aria-selected",a?"true":"false"),a&&r.classList.add("fi-selected");let c=document.createElement("span");return this.isHtmlAllowed?c.innerHTML=n:c.textContent=n,r.appendChild(c),o||r.addEventListener("click",h=>{h.preventDefault(),h.stopPropagation(),this.selectOption(i),this.isMultiple&&(this.isSearchable&&this.searchInput?setTimeout(()=>{this.searchInput.focus()},0):setTimeout(()=>{r.focus()},0))}),r}async updateSelectedDisplay(){this.selectedDisplayVersion=this.selectedDisplayVersion+1;let t=this.selectedDisplayVersion,e=document.createDocumentFragment();if(this.isMultiple){if(!Array.isArray(this.state)||this.state.length===0){let n=document.createElement("span");n.textContent=this.placeholder,n.classList.add("fi-select-input-placeholder"),e.appendChild(n)}else{let n=await this.getLabelsForMultipleSelection();if(t!==this.selectedDisplayVersion)return;this.addBadgesForSelectedOptions(n,e)}t===this.selectedDisplayVersion&&(this.selectedDisplay.replaceChildren(e),this.isOpen&&this.positionDropdown());return}if(this.state===null||this.state===""){let n=document.createElement("span");n.textContent=this.placeholder,n.classList.add("fi-select-input-placeholder"),e.appendChild(n),t===this.selectedDisplayVersion&&this.selectedDisplay.replaceChildren(e);return}let i=await this.getLabelForSingleSelection();t===this.selectedDisplayVersion&&(this.addSingleSelectionDisplay(i,e),t===this.selectedDisplayVersion&&this.selectedDisplay.replaceChildren(e))}async getLabelsForMultipleSelection(){let t=this.getSelectedOptionLabels(),e=[];if(Array.isArray(this.state)){for(let n of this.state)if(!y(this.labelRepository[n])){if(y(t[n])){this.labelRepository[n]=t[n];continue}e.push(n.toString())}}if(e.length>0&&y(this.initialOptionLabels)&&JSON.stringify(this.state)===JSON.stringify(this.initialState)){if(Array.isArray(this.initialOptionLabels))for(let n of this.initialOptionLabels)y(n)&&n.value!==void 0&&n.label!==void 0&&e.includes(n.value)&&(this.labelRepository[n.value]=n.label)}else if(e.length>0&&this.getOptionLabelsUsing)try{let n=await this.getOptionLabelsUsing();for(let o of n)y(o)&&o.value!==void 0&&o.label!==void 0&&(this.labelRepository[o.value]=o.label)}catch(n){console.error("Error fetching option labels:",n)}let i=[];if(Array.isArray(this.state))for(let n of this.state)y(this.labelRepository[n])?i.push(this.labelRepository[n]):y(t[n])?i.push(t[n]):i.push(n);return i}createBadgeElement(t,e){let i=document.createElement("span");i.className="fi-badge fi-size-md fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-200",y(t)&&i.setAttribute("data-value",t);let n=document.createElement("span");n.className="fi-badge-label-ctn";let o=document.createElement("span");o.className="fi-badge-label",this.canOptionLabelsWrap&&o.classList.add("fi-wrapped"),this.isHtmlAllowed?o.innerHTML=e:o.textContent=e,n.appendChild(o),i.appendChild(n);let r=this.createRemoveButton(t,e);return i.appendChild(r),i}createRemoveButton(t,e){let i=document.createElement("button");return i.type="button",i.className="fi-badge-delete-btn",i.innerHTML='<svg class="fi-icon fi-size-xs" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon"><path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"></path></svg>',i.setAttribute("aria-label","Remove "+(this.isHtmlAllowed?e.replace(/<[^>]*>/g,""):e)),i.addEventListener("click",n=>{n.stopPropagation(),y(t)&&this.selectOption(t)}),i.addEventListener("keydown",n=>{(n.key===" "||n.key==="Enter")&&(n.preventDefault(),n.stopPropagation(),y(t)&&this.selectOption(t))}),i}addBadgesForSelectedOptions(t,e=this.selectedDisplay){let i=document.createElement("div");i.className="fi-select-input-value-badges-ctn",t.forEach((n,o)=>{let r=Array.isArray(this.state)?this.state[o]:null,l=this.createBadgeElement(r,n);i.appendChild(l)}),e.appendChild(i)}async getLabelForSingleSelection(){let t=this.labelRepository[this.state];if(N(t)&&(t=this.getSelectedOptionLabel(this.state)),N(t)&&y(this.initialOptionLabel)&&this.state===this.initialState)t=this.initialOptionLabel,y(this.state)&&(this.labelRepository[this.state]=t);else if(N(t)&&this.getOptionLabelUsing)try{t=await this.getOptionLabelUsing(),y(t)&&y(this.state)&&(this.labelRepository[this.state]=t)}catch(e){console.error("Error fetching option label:",e),t=this.state}else N(t)&&(t=this.state);return t}addSingleSelectionDisplay(t,e=this.selectedDisplay){let i=document.createElement("span");if(i.className="fi-select-input-value-label",this.isHtmlAllowed?i.innerHTML=t:i.textContent=t,e.appendChild(i),!this.canSelectPlaceholder)return;let n=document.createElement("button");n.type="button",n.className="fi-select-input-value-remove-btn",n.innerHTML='<svg class="fi-icon fi-size-sm" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',n.setAttribute("aria-label","Clear selection"),n.addEventListener("click",o=>{o.stopPropagation(),this.selectOption("")}),n.addEventListener("keydown",o=>{(o.key===" "||o.key==="Enter")&&(o.preventDefault(),o.stopPropagation(),this.selectOption(""))}),e.appendChild(n)}getSelectedOptionLabel(t){if(y(this.labelRepository[t]))return this.labelRepository[t];let e="";for(let i of this.options)if(i.options&&Array.isArray(i.options)){for(let n of i.options)if(n.value===t){e=n.label,this.labelRepository[t]=e;break}}else if(i.value===t){e=i.label,this.labelRepository[t]=e;break}return e}setUpEventListeners(){this.buttonClickListener=()=>{this.toggleDropdown()},this.documentClickListener=t=>{!this.container.contains(t.target)&&this.isOpen&&this.closeDropdown()},this.buttonKeydownListener=t=>{this.isDisabled||this.handleSelectButtonKeydown(t)},this.dropdownKeydownListener=t=>{this.isDisabled||this.isSearchable&&document.activeElement===this.searchInput&&!["Tab","Escape"].includes(t.key)||this.handleDropdownKeydown(t)},this.selectButton.addEventListener("click",this.buttonClickListener),document.addEventListener("click",this.documentClickListener),this.selectButton.addEventListener("keydown",this.buttonKeydownListener),this.dropdown.addEventListener("keydown",this.dropdownKeydownListener),!this.isMultiple&&this.livewireId&&this.statePath&&this.getOptionLabelUsing&&(this.refreshOptionLabelListener=async t=>{if(t.detail.livewireId===this.livewireId&&t.detail.statePath===this.statePath&&y(this.state))try{delete this.labelRepository[this.state];let e=await this.getOptionLabelUsing();y(e)&&(this.labelRepository[this.state]=e);let i=this.selectedDisplay.querySelector(".fi-select-input-value-label");y(i)&&(this.isHtmlAllowed?i.innerHTML=e:i.textContent=e),this.updateOptionLabelInList(this.state,e)}catch(e){console.error("Error refreshing option label:",e)}},window.addEventListener("filament-forms::select.refreshSelectedOptionLabel",this.refreshOptionLabelListener))}updateOptionLabelInList(t,e){this.labelRepository[t]=e;let i=this.getVisibleOptions();for(let n of i)if(n.getAttribute("data-value")===String(t)){if(n.innerHTML="",this.isHtmlAllowed){let o=document.createElement("span");o.innerHTML=e,n.appendChild(o)}else n.appendChild(document.createTextNode(e));break}for(let n of this.options)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===t){o.label=e;break}}else if(n.value===t){n.label=e;break}for(let n of this.originalOptions)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===t){o.label=e;break}}else if(n.value===t){n.label=e;break}}handleSelectButtonKeydown(t){switch(t.key){case"ArrowDown":t.preventDefault(),t.stopPropagation(),this.isOpen?this.focusNextOption():this.openDropdown();break;case"ArrowUp":t.preventDefault(),t.stopPropagation(),this.isOpen?this.focusPreviousOption():this.openDropdown();break;case" ":if(t.preventDefault(),this.isOpen){if(this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}}else this.openDropdown();break;case"Enter":break;case"Escape":this.isOpen&&(t.preventDefault(),this.closeDropdown());break;case"Tab":this.isOpen&&this.closeDropdown();break;default:if(this.isSearchable&&!t.ctrlKey&&!t.metaKey&&!t.altKey&&typeof t.key=="string"&&t.key.length===1){t.preventDefault();let e=t.key;this.isOpen||this.openDropdown(),this.searchInput&&(this.searchInput.focus(),this.searchInput.value=(this.searchInput.value||"")+e,this.searchInput.dispatchEvent(new Event("input",{bubbles:!0})))}break}}handleDropdownKeydown(t){switch(t.key){case"ArrowDown":t.preventDefault(),t.stopPropagation(),this.focusNextOption();break;case"ArrowUp":t.preventDefault(),t.stopPropagation(),this.focusPreviousOption();break;case" ":if(t.preventDefault(),this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}break;case"Enter":if(t.preventDefault(),this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}else{let e=this.element.closest("form");e&&e.submit()}break;case"Escape":t.preventDefault(),this.closeDropdown(),this.selectButton.focus();break;case"Tab":this.closeDropdown();break;default:if(this.isSearchable&&!t.ctrlKey&&!t.metaKey&&!t.altKey&&typeof t.key=="string"&&t.key.length===1){t.preventDefault();let e=t.key;this.searchInput&&(this.searchInput.focus(),this.searchInput.value=(this.searchInput.value||"")+e,this.searchInput.dispatchEvent(new Event("input",{bubbles:!0})))}break}}toggleDropdown(){if(!this.isDisabled){if(this.isOpen){this.closeDropdown();return}this.isMultiple&&!this.isSearchable&&!this.hasAvailableOptions()||this.openDropdown()}}hasAvailableOptions(){for(let t of this.options)if(t.options&&Array.isArray(t.options)){for(let e of t.options)if(!Array.isArray(this.state)||!this.state.includes(e.value))return!0}else if(!Array.isArray(this.state)||!this.state.includes(t.value))return!0;return!1}async openDropdown(){this.dropdown.style.display="block",this.dropdown.style.opacity="0";let t=this.selectButton.closest(".fi-fixed-positioning-context")!==null&&this.selectButton.closest(".fi-absolute-positioning-context")===null;if(this.dropdown.style.position=t?"fixed":"absolute",this.dropdown.style.width=`${this.selectButton.offsetWidth}px`,this.selectButton.setAttribute("aria-expanded","true"),this.isOpen=!0,this.positionDropdown(),this.resizeListener||(this.resizeListener=()=>{this.dropdown.style.width=`${this.selectButton.offsetWidth}px`,this.positionDropdown()},window.addEventListener("resize",this.resizeListener)),this.scrollListener||(this.scrollListener=()=>this.positionDropdown(),window.addEventListener("scroll",this.scrollListener,!0)),this.dropdown.style.opacity="1",this.hasDynamicOptions&&this.getOptionsUsing){this.showLoadingState(!1);try{let e=await this.getOptionsUsing(),i=Array.isArray(e)?e:e&&Array.isArray(e.options)?e.options:[];this.options=i,this.originalOptions=JSON.parse(JSON.stringify(i)),this.populateLabelRepositoryFromOptions(i),this.renderOptions()}catch(e){console.error("Error fetching options:",e),this.hideLoadingState()}}if(this.hideLoadingState(),this.isSearchable&&this.searchInput)this.searchInput.value="",this.searchInput.focus(),this.searchQuery="",this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions();else{this.selectedIndex=-1;let e=this.getVisibleOptions();if(this.isMultiple){if(Array.isArray(this.state)&&this.state.length>0){for(let i=0;i<e.length;i++)if(this.state.includes(e[i].getAttribute("data-value"))){this.selectedIndex=i;break}}}else for(let i=0;i<e.length;i++)if(e[i].getAttribute("data-value")===this.state){this.selectedIndex=i;break}this.selectedIndex===-1&&e.length>0&&(this.selectedIndex=0),this.selectedIndex>=0&&(e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus())}}positionDropdown(){let t=this.position==="top"?"top-start":"bottom-start",e=[Jt(4),jt({padding:5})];this.position!=="top"&&this.position!=="bottom"&&e.push(qt());let i=this.selectButton.closest(".fi-fixed-positioning-context")!==null&&this.selectButton.closest(".fi-absolute-positioning-context")===null;Xt(this.selectButton,this.dropdown,{placement:t,middleware:e,strategy:i?"fixed":"absolute"}).then(({x:n,y:o})=>{Object.assign(this.dropdown.style,{left:`${n}px`,top:`${o}px`})})}closeDropdown(){this.dropdown.style.display="none",this.selectButton.setAttribute("aria-expanded","false"),this.isOpen=!1,this.resizeListener&&(window.removeEventListener("resize",this.resizeListener),this.resizeListener=null),this.scrollListener&&(window.removeEventListener("scroll",this.scrollListener,!0),this.scrollListener=null),this.getVisibleOptions().forEach(e=>{e.classList.remove("fi-selected")})}focusNextOption(){let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex>=0&&this.selectedIndex<t.length&&t[this.selectedIndex].classList.remove("fi-selected"),this.selectedIndex===t.length-1&&this.isSearchable&&this.searchInput){this.selectedIndex=-1,this.searchInput.focus(),this.dropdown.removeAttribute("aria-activedescendant");return}this.selectedIndex=(this.selectedIndex+1)%t.length,t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus(),t[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",t[this.selectedIndex].id),this.scrollOptionIntoView(t[this.selectedIndex])}}focusPreviousOption(){let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex>=0&&this.selectedIndex<t.length&&t[this.selectedIndex].classList.remove("fi-selected"),(this.selectedIndex===0||this.selectedIndex===-1)&&this.isSearchable&&this.searchInput){this.selectedIndex=-1,this.searchInput.focus(),this.dropdown.removeAttribute("aria-activedescendant");return}this.selectedIndex=(this.selectedIndex-1+t.length)%t.length,t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus(),t[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",t[this.selectedIndex].id),this.scrollOptionIntoView(t[this.selectedIndex])}}scrollOptionIntoView(t){if(!t)return;let e=this.dropdown.getBoundingClientRect(),i=t.getBoundingClientRect();i.bottom>e.bottom?this.dropdown.scrollTop+=i.bottom-e.bottom:i.top<e.top&&(this.dropdown.scrollTop-=e.top-i.top)}getVisibleOptions(){let t=[];this.optionsList.classList.contains("fi-dropdown-list")?t=Array.from(this.optionsList.querySelectorAll(':scope > li[role="option"]')):t=Array.from(this.optionsList.querySelectorAll(':scope > ul.fi-dropdown-list > li[role="option"]'));let e=Array.from(this.optionsList.querySelectorAll('li.fi-select-input-option-group > ul > li[role="option"]'));return[...t,...e]}getSelectedOptionLabels(){if(!Array.isArray(this.state)||this.state.length===0)return{};let t={};for(let e of this.state){let i=!1;for(let n of this.options)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===e){t[e]=o.label,i=!0;break}if(i)break}else if(n.value===e){t[e]=n.label,i=!0;break}}return t}handleSearch(t){let e=t.target.value.trim();if(this.searchQuery=e,this.searchTimeout&&clearTimeout(this.searchTimeout),e===""){this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions();return}if(!this.getSearchResultsUsing||typeof this.getSearchResultsUsing!="function"||!this.hasDynamicSearchResults){this.filterOptions(e);return}this.searchTimeout=setTimeout(async()=>{this.searchTimeout=null,this.isSearching=!0;try{this.showLoadingState(!0);let i=await this.getSearchResultsUsing(e),n=Array.isArray(i)?i:i&&Array.isArray(i.options)?i.options:[];this.options=n,this.populateLabelRepositoryFromOptions(n),this.hideLoadingState(),this.renderOptions(),this.isOpen&&this.positionDropdown(),this.options.length===0&&this.showNoResultsMessage()}catch(i){console.error("Error fetching search results:",i),this.hideLoadingState(),this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions()}finally{this.isSearching=!1}},this.searchDebounce)}showLoadingState(t=!1){this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList),this.hideLoadingState();let e=document.createElement("div");e.className="fi-select-input-message",e.textContent=t?this.searchingMessage:this.loadingMessage,this.dropdown.appendChild(e)}hideLoadingState(){let t=this.dropdown.querySelector(".fi-select-input-message");t&&t.remove()}showNoResultsMessage(){this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList),this.hideLoadingState();let t=document.createElement("div");t.className="fi-select-input-message",t.textContent=this.noSearchResultsMessage,this.dropdown.appendChild(t)}filterOptions(t){let e=this.searchableOptionFields.includes("label"),i=this.searchableOptionFields.includes("value");t=t.toLowerCase();let n=[];for(let o of this.originalOptions)if(o.options&&Array.isArray(o.options)){let r=o.options.filter(l=>e&&l.label.toLowerCase().includes(t)||i&&String(l.value).toLowerCase().includes(t));r.length>0&&n.push({label:o.label,options:r})}else(e&&o.label.toLowerCase().includes(t)||i&&String(o.value).toLowerCase().includes(t))&&n.push(o);this.options=n,this.renderOptions(),this.options.length===0&&this.showNoResultsMessage(),this.isOpen&&this.positionDropdown()}selectOption(t){if(this.isDisabled)return;if(!this.isMultiple){this.state=t,this.updateSelectedDisplay(),this.renderOptions(),this.closeDropdown(),this.selectButton.focus(),this.onStateChange(this.state);return}let e=Array.isArray(this.state)?[...this.state]:[];if(e.includes(t)){let n=this.selectedDisplay.querySelector(`[data-value="${t}"]`);if(y(n)){let o=n.parentElement;y(o)&&o.children.length===1?(e=e.filter(r=>r!==t),this.state=e,this.updateSelectedDisplay()):(n.remove(),e=e.filter(r=>r!==t),this.state=e)}else e=e.filter(o=>o!==t),this.state=e,this.updateSelectedDisplay();this.renderOptions(),this.isOpen&&this.positionDropdown(),this.maintainFocusInMultipleMode(),this.onStateChange(this.state);return}if(this.maxItems&&e.length>=this.maxItems){this.maxItemsMessage&&alert(this.maxItemsMessage);return}e.push(t),this.state=e;let i=this.selectedDisplay.querySelector(".fi-select-input-value-badges-ctn");N(i)?this.updateSelectedDisplay():this.addSingleBadge(t,i),this.renderOptions(),this.isOpen&&this.positionDropdown(),this.maintainFocusInMultipleMode(),this.onStateChange(this.state)}async addSingleBadge(t,e){let i=this.labelRepository[t];if(N(i)&&(i=this.getSelectedOptionLabel(t),y(i)&&(this.labelRepository[t]=i)),N(i)&&this.getOptionLabelsUsing)try{let o=await this.getOptionLabelsUsing();for(let r of o)if(y(r)&&r.value===t&&r.label!==void 0){i=r.label,this.labelRepository[t]=i;break}}catch(o){console.error("Error fetching option label:",o)}N(i)&&(i=t);let n=this.createBadgeElement(t,i);e.appendChild(n)}maintainFocusInMultipleMode(){if(this.isSearchable&&this.searchInput){this.searchInput.focus();return}let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex=-1,Array.isArray(this.state)&&this.state.length>0){for(let e=0;e<t.length;e++)if(this.state.includes(t[e].getAttribute("data-value"))){this.selectedIndex=e;break}}this.selectedIndex===-1&&(this.selectedIndex=0),t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus()}}disable(){this.isDisabled||(this.isDisabled=!0,this.applyDisabledState(),this.isOpen&&this.closeDropdown())}enable(){this.isDisabled&&(this.isDisabled=!1,this.applyDisabledState())}applyDisabledState(){if(this.isDisabled){if(this.selectButton.setAttribute("disabled","disabled"),this.selectButton.setAttribute("aria-disabled","true"),this.selectButton.classList.add("fi-disabled"),this.isMultiple&&this.container.querySelectorAll(".fi-select-input-badge-remove").forEach(e=>{e.setAttribute("disabled","disabled"),e.classList.add("fi-disabled")}),!this.isMultiple&&this.canSelectPlaceholder){let t=this.container.querySelector(".fi-select-input-value-remove-btn");t&&(t.setAttribute("disabled","disabled"),t.classList.add("fi-disabled"))}this.isSearchable&&this.searchInput&&(this.searchInput.setAttribute("disabled","disabled"),this.searchInput.classList.add("fi-disabled"))}else{if(this.selectButton.removeAttribute("disabled"),this.selectButton.removeAttribute("aria-disabled"),this.selectButton.classList.remove("fi-disabled"),this.isMultiple&&this.container.querySelectorAll(".fi-select-input-badge-remove").forEach(e=>{e.removeAttribute("disabled"),e.classList.remove("fi-disabled")}),!this.isMultiple&&this.canSelectPlaceholder){let t=this.container.querySelector(".fi-select-input-value-remove-btn");t&&(t.removeAttribute("disabled"),t.classList.add("fi-disabled"))}this.isSearchable&&this.searchInput&&(this.searchInput.removeAttribute("disabled"),this.searchInput.classList.remove("fi-disabled"))}}destroy(){this.selectButton&&this.buttonClickListener&&this.selectButton.removeEventListener("click",this.buttonClickListener),this.documentClickListener&&document.removeEventListener("click",this.documentClickListener),this.selectButton&&this.buttonKeydownListener&&this.selectButton.removeEventListener("keydown",this.buttonKeydownListener),this.dropdown&&this.dropdownKeydownListener&&this.dropdown.removeEventListener("keydown",this.dropdownKeydownListener),this.resizeListener&&(window.removeEventListener("resize",this.resizeListener),this.resizeListener=null),this.scrollListener&&(window.removeEventListener("scroll",this.scrollListener,!0),this.scrollListener=null),this.refreshOptionLabelListener&&window.removeEventListener("filament-forms::select.refreshSelectedOptionLabel",this.refreshOptionLabelListener),this.isOpen&&this.closeDropdown(),this.searchTimeout&&(clearTimeout(this.searchTimeout),this.searchTimeout=null),this.container&&this.container.remove()}};/**
 * Fabrique et initialise une instance de composant select configurable avec gestion d'état, recherche et support multi-sélection.
 *
 * Crée et retourne un objet qui expose la propriété `select` (instance interne, initialement null), la `state` fournie et deux méthodes `init()` et `destroy()` pour monter/démonter le composant.
 *
 * @param {Object} options - Options de configuration du composant.
 * @param {*} options.state - État initial partagé du composant (valeur ou structure utilisée pour la sélection).
 * @param {Array|Function} [options.options] - Liste statique d'options ou fournisseur asynchrone d'options.
 * @param {boolean} [options.isMultiple=false] - Active la sélection multiple.
 * @param {boolean} [options.isSearchable=false] - Active le champ de recherche.
 * @param {boolean} [options.isAutofocused=false] - Met le composant en focus au rendu initial.
 * @param {boolean} [options.isDisabled=false] - Démarre le composant en état désactivé.
 * @param {boolean} [options.canOptionLabelsWrap=false] - Permet le retour à la ligne des labels d'options dans l'affichage.
 * @param {boolean} [options.canSelectPlaceholder=true] - Permet l'affichage d'un placeholder sélectionnable.
 * @param {boolean} [options.isHtmlAllowed=false] - Autorise le rendu HTML dans les labels d'options.
 * @param {Function} [options.getOptionLabelUsing] - Fonction pour résoudre le label d'une option unique.
 * @param {Function} [options.getOptionLabelsUsing] - Fonction pour résoudre plusieurs labels d'options.
 * @param {Function} [options.getOptionsUsing] - Fonction asynchrone pour charger dynamiquement les options.
 * @param {Function} [options.getSearchResultsUsing] - Fonction asynchrone pour effectuer la recherche côté serveur.
 * @param {boolean} [options.hasDynamicOptions=false] - Indique que les options sont chargées dynamiquement.
 * @param {boolean} [options.hasDynamicSearchResults=false] - Indique que les résultats de recherche sont dynamiques.
 * @param {string} [options.livewireId] - Identifiant utilisé pour intégration Livewire (si présent).
 * @param {string} [options.placeholder] - Texte du placeholder affiché.
 * @param {string} [options.initialOptionLabel] - Label initial pour une sélection unique.
 * @param {Array} [options.initialOptionLabels] - Labels initiaux pour une sélection multiple.
 * @param {Object} [options.initialState] - État initial interne du composant.
 * @param {number} [options.maxItems] - Nombre maximum d'éléments sélectionnables en multi-sélection.
 * @param {string} [options.maxItemsMessage] - Message affiché lorsque la limite max est atteinte.
 * @param {number} [options.optionsLimit] - Limite d'options affichées/retournées par page (pagination locale/affichage).
 * @param {string} [options.position] - Positionnement du dropdown (transmis au moteur de positionnement).
 * @param {number} [options.searchDebounce] - Délai de debounce pour la recherche locale/serveur (ms).
 * @param {string} [options.loadingMessage] - Message affiché pendant le chargement.
 * @param {string} [options.searchingMessage] - Message affiché pendant la recherche asynchrone.
 * @param {string} [options.noSearchResultsMessage] - Message affiché quand la recherche ne retourne rien.
 * @param {string} [options.searchPrompt] - Texte d'indication pour la recherche.
 * @param {Array<string>} [options.searchableOptionFields] - Champs d'option à considérer lors du filtrage local.
 * @param {string} [options.statePath] - Chemin d'état utilisé pour l'intégration (si applicable).
 *
 * @returns {Object} Objet de contrôle du composant select contenant :
 *  - `select`: instance interne du composant (ou null avant init),
 *  - `state`: référence à l'état partagé fourni,
 *  - `init()`: méthode pour initialiser et monter le composant,
 *  - `destroy()`: méthode pour démonter et nettoyer le composant.
 */
function Ee({canOptionLabelsWrap:s,canSelectPlaceholder:t,isHtmlAllowed:e,getOptionLabelUsing:i,getOptionLabelsUsing:n,getOptionsUsing:o,getSearchResultsUsing:r,initialOptionLabel:l,initialOptionLabels:a,initialState:c,isAutofocused:h,isDisabled:d,isMultiple:p,isSearchable:f,hasDynamicOptions:u,hasDynamicSearchResults:m,livewireId:g,loadingMessage:w,maxItems:b,maxItemsMessage:v,noSearchResultsMessage:L,options:x,optionsLimit:J,placeholder:Q,position:W,searchDebounce:F,searchingMessage:I,searchPrompt:j,searchableOptionFields:q,state:X,statePath:A}){return{select:null,state:X,init(){this.select=new ut({element:this.$refs.select,options:x,placeholder:Q,state:this.state,canOptionLabelsWrap:s,canSelectPlaceholder:t,initialOptionLabel:l,initialOptionLabels:a,initialState:c,isHtmlAllowed:e,isAutofocused:h,isDisabled:d,isMultiple:p,isSearchable:f,getOptionLabelUsing:i,getOptionLabelsUsing:n,getOptionsUsing:o,getSearchResultsUsing:r,hasDynamicOptions:u,hasDynamicSearchResults:m,searchPrompt:j,searchDebounce:F,loadingMessage:w,searchingMessage:I,noSearchResultsMessage:L,maxItems:b,maxItemsMessage:v,optionsLimit:J,position:W,searchableOptionFields:q,livewireId:g,statePath:A,onStateChange:k=>{this.state=k}}),this.$watch("state",k=>{this.select&&this.select.state!==k&&(this.select.state=k,this.select.updateSelectedDisplay(),this.select.renderOptions())})},destroy(){this.select&&(this.select.destroy(),this.select=null)}}}export{Ee as default};