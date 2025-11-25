var tt=Math.min,$=Math.max,et=Math.round;var k=s=>({x:s,y:s}),Gt={left:"right",right:"left",bottom:"top",top:"bottom"},Qt={start:"end",end:"start"};/**
 * Combine s avec le résultat d'une transformation appliquée à t et e, puis transmet ces valeurs à l'opération de traitement.
 *
 * @param {*} s - Première valeur fournie à l'opération de traitement.
 * @param {*} t - Valeur soumise à une transformation avant traitement.
 * @param {*} e - Second paramètre utilisé par la transformation appliquée à `t`.
 * @returns {*} Le résultat renvoyé par l'opération de traitement après combinaison des valeurs.
 */
function mt(s,t,e){return $(s,tt(t,e))}/**
 * Évalue une valeur potentiellement paresseuse.
 *
 * Si `s` est une fonction, elle est appelée avec `t` et son résultat est renvoyé ; sinon `s` est renvoyé tel quel.
 * @param {*} s - Une valeur ou une fonction. Si c'est une fonction, elle recevra `t` comme argument.
 * @param {*} t - Argument à transmettre à `s` si `s` est une fonction.
 * @returns {*} Le résultat de `s(t)` si `s` est une fonction, sinon la valeur `s`.
 */
function it(s,t){return typeof s=="function"?s(t):s}/**
 * Retourne la partie d'une chaîne située avant le premier trait d'union.
 * @param {string} s - Chaîne d'entrée.
 * @returns {string} La sous-chaîne située avant le premier '-' ; si aucun '-' n'est présent, renvoie la chaîne entière.
 */
function z(s){return s.split("-")[0]}/**
 * Récupère la portion située après le premier tiret ("-") dans une chaîne.
 * @param {string} s - Chaîne à découper.
 * @returns {string|undefined} La sous-chaîne après le premier tiret, `undefined` si aucun tiret n'est présent.
 */
function st(s){return s.split("-")[1]}/**
 * Inverse un axe entre "x" et "y".
 * @param {string} s - Axe courant, attendu "x" ou "y".
 * @returns {string} "y" si `s` vaut "x", "x" sinon.
 */
function gt(s){return s==="x"?"y":"x"}/**
 * Renvoie le nom de la dimension correspondant à l'axe fourni.
 * @param {string} s - Lettre représentant l'axe : `"y"` pour l'axe vertical, autre chose pour l'axe horizontal.
 * @returns {string} `"height"` si `s` est `"y"`, `"width"` sinon.
 */
function bt(s){return s==="y"?"height":"width"}var Zt=new Set(["top","bottom"]);/**
 * Détermine l'axe principal (`'x'` ou `'y'`) correspondant à un emplacement donné.
 * @param {string} s - Chaîne décrivant l'emplacement (par exemple `'top'`, `'left'`, etc.).
 * @returns {string} `'y'` si l'emplacement appartient au groupe vertical, `'x'` sinon.
 */
function B(s){return Zt.has(z(s))?"y":"x"}/**
 * Basculer l'axe d'une valeur de placement.
 *
 * @param {string} s - Valeur de placement à traiter (par ex. 'top', 'bottom', 'left', 'right' ou variantes).
 * @returns {string} La valeur de placement résultante avec l'axe inversé.
 */
function yt(s){return gt(B(s))}/**
 * Détermine une orientation de placement principale et son opposé en fonction d'un placement souhaité et des positions des boîtes reference et floating.
 *
 * @param {string} s - Placement souhaité (par ex. "start", "end", etc.).
 * @param {{ reference: Record<string, number>, floating: Record<string, number> }} t - Objets contenant les coordonnées/mesures pour les boîtes `reference` et `floating`; les clés attendues dépendent de l'axe calculé à partir de `s`.
 * @param {boolean} [e=false] - Indicateur qui inverse l'interprétation des extrémités pour le calcul initial (active l'usage de "end" au lieu de "start" pour l'axe concerné).
 * @returns {[string,string]} Un tableau contenant la placement choisi en premier élément et le placement opposé en second élément.
 */
function Ot(s,t,e){e===void 0&&(e=!1);let i=st(s),n=yt(s),o=bt(n),r=n==="x"?i===(e?"end":"start")?"right":"left":i==="start"?"bottom":"top";return t.reference[o]>t.floating[o]&&(r=Z(r)),[r,Z(r)]}/**
 * Fournit une triple composée de la forme normalisée d'un placement, d'une transformation associée et de la forme normalisée de cette transformation.
 *
 * @param {string} s - Chaîne décrivant un placement (par exemple "top-start").
 * @returns {Array<string>} Un tableau [a, b, c] où `a` est la version normalisée de `s`, `b` est la transformation calculée à partir de `s`, et `c` est la version normalisée de `b`.
 */
function At(s){let t=Z(s);return[lt(s),t,lt(t)]}/**
 * Remplace les occurrences de 'start' et 'end' dans la chaîne par leurs équivalents directionnels.
 * @param {string} s - Chaîne pouvant contenir les tokens 'start' et/ou 'end'.
 * @returns {string} La chaîne modifiée où chaque 'start' et 'end' est remplacé par son équivalent directionnel.
 */
function lt(s){return s.replace(/start|end/g,t=>Qt[t])}var vt=["left","right"],Lt=["right","left"],te=["top","bottom"],ee=["bottom","top"];/**
 * Retourne l'ordre d'alignements candidats en fonction du côté et de deux indicateurs.
 *
 * @param {string} s - Le côté de placement attendu : `"top"`, `"bottom"`, `"left"` ou `"right"`.
 * @param {boolean} t - Indicateur de préférence d'axe (par ex. priorité start vs end).
 * @param {boolean} e - Indicateur d'inversion de l'ordre d'alignement.
 * @returns {string[]} Tableau des clés d'alignement dans l'ordre de préférence, ou tableau vide si `s` n'est pas reconnu.
 */
function ie(s,t,e){switch(s){case"top":case"bottom":return e?t?Lt:vt:t?vt:Lt;case"left":case"right":return t?te:ee;default:return[]}}/**
 * Produit une liste de placements dérivés à partir d'un placement source, en préfixant un modificateur et en option en ajoutant les variantes inversées.
 *
 * @param {string} s - Placement source (par ex. `"top"`, `"left-start"`).
 * @param {boolean} t - Si `true`, ajoute les variantes inversées des placements générés.
 * @param {string} e - Alignement de référence (la valeur `"start"` influence la génération des alignements).
 * @param {*} i - Option supplémentaire transmise au générateur d'alignements.
 * @returns {Array<string>|undefined} La liste des placements dérivés (ex. `["x-top", "x-top-start"]`), ou `undefined` si aucun modificateur n'a été extrait de `s`.
 */
function St(s,t,e,i){let n=st(s),o=ie(z(s),e==="start",i);return n&&(o=o.map(r=>r+"-"+n),t&&(o=o.concat(o.map(lt)))),o}/**
 * Remplace les directions 'left', 'right', 'top' et 'bottom' dans une chaîne par leurs équivalents.
 * @param {string} s - Chaîne contenant éventuellement les mots `left`, `right`, `top` ou `bottom`.
 * @returns {string} La chaîne résultante avec ces mots remplacés par leurs valeurs correspondantes définies dans `Gt`.
 */
function Z(s){return s.replace(/left|right|bottom|top/g,t=>Gt[t])}/**
 * Crée un objet d'insets avec des valeurs par défaut à 0 et applique les remplacements fournis.
 * @param {Object} s - Objet optionnel contenant une ou plusieurs des propriétés `top`, `right`, `bottom`, `left` (valeurs numériques).
 * @returns {{top:number,right:number,bottom:number,left:number}} Un objet contenant `top`, `right`, `bottom` et `left` : chaque valeur provient de `s` si fournie, sinon vaut `0`.
 */
function se(s){return{top:0,right:0,bottom:0,left:0,...s}}/**
 * Normalise une valeur d'espacement en un objet {top, right, bottom, left}.
 *
 * Si `s` est un nombre, chaque côté reçoit cette valeur. Sinon, la valeur
 * est interprétée comme une notation d'espacement (shorthand) et convertie
 * en propriétés `top`, `right`, `bottom` et `left`.
 *
 * @param {number|string|*} s - Valeur d'espacement : un nombre (appliqué à tous les côtés)
 *                              ou une représentation shorthand/complexe qui sera convertie.
 * @returns {{top: number, right: number, bottom: number, left: number}} Objet d'espacements par côté.
 */
function Ct(s){return typeof s!="number"?se(s):{top:s,right:s,bottom:s,left:s}}/**
 * Crée un objet rectangle normalisé à partir de coordonnées et de dimensions.
 * @param {{x:number,y:number,width:number,height:number}} s - Coordonnées (x,y) et dimensions du rectangle.
 * @returns {{width:number,height:number,top:number,left:number,right:number,bottom:number,x:number,y:number}} Un objet contenant width, height, top, left, right, bottom ainsi que x et y représentant l'origine.
 */
function U(s){let{x:t,y:e,width:i,height:n}=s;return{width:i,height:n,top:e,left:t,right:t+i,bottom:e+n,x:t,y:e}}/**
 * Calcule les coordonnées x/y pour positionner un élément flottant par rapport à une référence selon le placement et l'alignement.
 *
 * @param {{reference: {x:number,y:number,width:number,height:number}, floating: {width:number,height:number}}} s - Objets de mesures : `reference` (rect de l'élément de référence) et `floating` (rect de l'élément flottant).
 * @param {string} t - Chaîne de placement (par ex. "top", "bottom", "left", "right", éventuellement suivie de "-start" ou "-end" pour l'alignement).
 * @param {boolean} e - Indique si l'alignement doit être inversé sur l'axe secondaire (p. ex. pour le RTL).
 * @returns {{x:number,y:number}} Coordonnées calculées { x, y } pour positionner l'élément flottant.
 */
function Dt(s,t,e){let{reference:i,floating:n}=s,o=B(t),r=yt(t),l=bt(r),a=z(t),c=o==="y",h=i.x+i.width/2-n.width/2,d=i.y+i.height/2-n.height/2,u=i[l]/2-n[l]/2,f;switch(a){case"top":f={x:h,y:i.y-n.height};break;case"bottom":f={x:h,y:i.y+i.height};break;case"right":f={x:i.x+i.width,y:d};break;case"left":f={x:i.x-n.width,y:d};break;default:f={x:i.x,y:i.y}}switch(st(t)){case"start":f[r]-=u*(e&&c?-1:1);break;case"end":f[r]+=u*(e&&c?-1:1);break}return f}var Et=async(s,t,e)=>{let{placement:i="bottom",strategy:n="absolute",middleware:o=[],platform:r}=e,l=o.filter(Boolean),a=await(r.isRTL==null?void 0:r.isRTL(t)),c=await r.getElementRects({reference:s,floating:t,strategy:n}),{x:h,y:d}=Dt(c,i,a),u=i,f={},p=0;for(let m=0;m<l.length;m++){let{name:g,fn:w}=l[m],{x:b,y:v,data:O,reset:x}=await w({x:h,y:d,initialPlacement:i,placement:u,strategy:n,middlewareData:f,rects:c,platform:r,elements:{reference:s,floating:t}});h=b??h,d=v??d,f={...f,[g]:{...f[g],...O}},x&&p<=50&&(p++,typeof x=="object"&&(x.placement&&(u=x.placement),x.rects&&(c=x.rects===!0?await r.getElementRects({reference:s,floating:t,strategy:n}):x.rects),{x:h,y:d}=Dt(c,u,a)),m=-1)}return{x:h,y:d,placement:u,strategy:n,middlewareData:f}};/**
 * Calcule les distances (marges d'overflow) entre un rectangle de clipping et le rectangle fourni,
 * en tenant compte du padding, de la stratégie et de l'échelle de l'offset parent.
 *
 * @param {Object} s - Contexte requis pour le calcul.
 * @param {number} s.x - Position x (coordonnée horizontale) du point de référence utilisé pour le calcul.
 * @param {number} s.y - Position y (coordonnée verticale) du point de référence utilisé pour le calcul.
 * @param {Object} s.platform - Plateforme/abstraction DOM offrant les méthodes de mesure et conversion.
 * @param {Object} s.rects - Rectangles de référence { reference, floating } utilisés pour les calculs.
 * @param {Object} s.elements - Éléments { reference, floating, floating.contextElement? } impliqués.
 * @param {string} s.strategy - Stratégie de positionnement (par ex. "absolute" ou "fixed").
 * @param {Object} [t] - Options de calcul.
 * @param {('clippingAncestors'|'viewport'|'document'|Element)} [t.boundary='clippingAncestors'] - Définition de la frontière utilisée pour le clipping.
 * @param {('viewport'|'document')} [t.rootBoundary='viewport'] - Bordure racine à utiliser.
 * @param {('reference'|'floating')} [t.elementContext='floating'] - Contexte d'élément pour lequel calculer le clipping.
 * @param {boolean} [t.altBoundary=false] - Utiliser la frontière alternative (basée sur l'élément opposé).
 * @param {number|Object} [t.padding=0] - Padding appliqué au rectangle de clipping (nombre uniforme ou objet {top,right,bottom,left}).
 * @returns {{top:number,bottom:number,left:number,right:number}} Distances en pixels (après ajustement de l'échelle de l'offset parent) :
 *  - top : distance entre le bord supérieur du clipping et le bord supérieur de l'élément (positive si de l'espace est disponible au-dessus).
 *  - bottom : distance entre le bord inférieur de l'élément et le bord inférieur du clipping (positive si de l'espace est disponible en dessous).
 *  - left : distance entre le bord gauche du clipping et le bord gauche de l'élément (positive si de l'espace est disponible à gauche).
 *  - right : distance entre le bord droit de l'élément et le bord droit du clipping (positive si de l'espace est disponible à droite).
 */
async function wt(s,t){var e;t===void 0&&(t={});let{x:i,y:n,platform:o,rects:r,elements:l,strategy:a}=s,{boundary:c="clippingAncestors",rootBoundary:h="viewport",elementContext:d="floating",altBoundary:u=!1,padding:f=0}=it(t,s),p=Ct(f),g=l[u?d==="floating"?"reference":"floating":d],w=U(await o.getClippingRect({element:(e=await(o.isElement==null?void 0:o.isElement(g)))==null||e?g:g.contextElement||await(o.getDocumentElement==null?void 0:o.getDocumentElement(l.floating)),boundary:c,rootBoundary:h,strategy:a})),b=d==="floating"?{x:i,y:n,width:r.floating.width,height:r.floating.height}:r.reference,v=await(o.getOffsetParent==null?void 0:o.getOffsetParent(l.floating)),O=await(o.isElement==null?void 0:o.isElement(v))?await(o.getScale==null?void 0:o.getScale(v))||{x:1,y:1}:{x:1,y:1},x=U(o.convertOffsetParentRelativeRectToViewportRelativeRect?await o.convertOffsetParentRelativeRectToViewportRelativeRect({elements:l,rect:b,offsetParent:v,strategy:a}):b);return{top:(w.top-x.top+p.top)/O.y,bottom:(x.bottom-w.bottom+p.bottom)/O.y,left:(w.left-x.left+p.left)/O.x,right:(x.right-w.right+p.right)/O.x}}var Rt=function(s){return s===void 0&&(s={}),{name:"flip",options:s,async fn(t){var e,i;let{placement:n,middlewareData:o,rects:r,initialPlacement:l,platform:a,elements:c}=t,{mainAxis:h=!0,crossAxis:d=!0,fallbackPlacements:u,fallbackStrategy:f="bestFit",fallbackAxisSideDirection:p="none",flipAlignment:m=!0,...g}=it(s,t);if((e=o.arrow)!=null&&e.alignmentOffset)return{};let w=z(n),b=B(l),v=z(l)===l,O=await(a.isRTL==null?void 0:a.isRTL(c.floating)),x=u||(v||!m?[Z(l)]:At(l)),J=p!=="none";!u&&J&&x.push(...St(l,m,p,O));let Y=[l,...x],W=await wt(t,g),L=[],S=((i=o.flip)==null?void 0:i.overflows)||[];if(h&&L.push(W[w]),d){let E=Ot(n,r,O);L.push(W[E[0]],W[E[1]])}if(S=[...S,{placement:n,overflows:L}],!L.every(E=>E<=0)){var V,G;let E=(((V=o.flip)==null?void 0:V.index)||0)+1,j=Y[E];if(j&&(!(d==="alignment"?b!==B(j):!1)||S.every(I=>B(I.placement)===b?I.overflows[0]>0:!0)))return{data:{index:E,overflows:S},reset:{placement:j}};let R=(G=S.filter(M=>M.overflows[0]<=0).sort((M,I)=>M.overflows[1]-I.overflows[1])[0])==null?void 0:G.placement;if(!R)switch(f){case"bestFit":{var Q;let M=(Q=S.filter(I=>{if(J){let H=B(I.placement);return H===b||H==="y"}return!0}).map(I=>[I.placement,I.overflows.filter(H=>H>0).reduce((H,Yt)=>H+Yt,0)]).sort((I,H)=>I[1]-H[1])[0])==null?void 0:Q[0];M&&(R=M);break}case"initialPlacement":R=l;break}if(n!==R)return{reset:{placement:R}}}return{}}}};var ne=new Set(["left","top"]);/**
 * Calcule les décalages horizontaux (x) et verticaux (y) pour un placement donné en tenant compte du sens RTL, de l'axe principal/croisé et des options d'alignement.
 * @param {Object} s - Contexte de placement.
 * @param {string} s.placement - Nom du placement (par ex. "top-start", "right", ...).
 * @param {Object} s.platform - Interface plateforme pouvant exposer la propriété `isRTL`.
 * @param {Object} s.elements - Collection d'éléments impliqués (utilisé pour déterminer le floating element).
 * @param {*} t - Valeur d'offset fournie (nombre, objet d'axes ou fonction résolvant ces valeurs).
 * @returns {{x:number,y:number}} Les décalages calculés le long des axes X et Y. 
 */
async function oe(s,t){let{placement:e,platform:i,elements:n}=s,o=await(i.isRTL==null?void 0:i.isRTL(n.floating)),r=z(e),l=st(e),a=B(e)==="y",c=ne.has(r)?-1:1,h=o&&a?-1:1,d=it(t,s),{mainAxis:u,crossAxis:f,alignmentAxis:p}=typeof d=="number"?{mainAxis:d,crossAxis:0,alignmentAxis:null}:{mainAxis:d.mainAxis||0,crossAxis:d.crossAxis||0,alignmentAxis:d.alignmentAxis};return l&&typeof p=="number"&&(f=l==="end"?p*-1:p),a?{x:f*h,y:u*c}:{x:u*c,y:f*h}}var It=function(s){return s===void 0&&(s=0),{name:"offset",options:s,async fn(t){var e,i;let{x:n,y:o,placement:r,middlewareData:l}=t,a=await oe(t,s);return r===((e=l.offset)==null?void 0:e.placement)&&(i=l.arrow)!=null&&i.alignmentOffset?{}:{x:n+a.x,y:o+a.y,data:{...a,placement:r}}}}},kt=function(s){return s===void 0&&(s={}),{name:"shift",options:s,async fn(t){let{x:e,y:i,placement:n}=t,{mainAxis:o=!0,crossAxis:r=!1,limiter:l={fn:g=>{let{x:w,y:b}=g;return{x:w,y:b}}},...a}=it(s,t),c={x:e,y:i},h=await wt(t,a),d=B(z(n)),u=gt(d),f=c[u],p=c[d];if(o){let g=u==="y"?"top":"left",w=u==="y"?"bottom":"right",b=f+h[g],v=f-h[w];f=mt(b,f,v)}if(r){let g=d==="y"?"top":"left",w=d==="y"?"bottom":"right",b=p+h[g],v=p-h[w];p=mt(b,p,v)}let m=l.fn({...t,[u]:f,[d]:p});return{...m,data:{x:m.x-e,y:m.y-i,enabled:{[u]:o,[d]:r}}}}}};/**
 * Détecte si l'environnement fournit un objet global `window`.
 * @returns {boolean} `true` si l'objet global `window` est défini, `false` sinon.
 */
function ct(){return typeof window<"u"}/**
 * Renvoie le nom du nœud en minuscules pour un nœud/élément DOM, ou "#document" pour un document.
 *
 * @param {*} s - Le nœud ou objet à examiner.
 * @returns {string} Le nom du nœud en minuscules (par ex. "div") si `s` représente un nœud/élément, "#document" sinon.
 */
function K(s){return Pt(s)?(s.nodeName||"").toLowerCase():"#document"}/**
 * Récupère la fenêtre (defaultView) du document propriétaire d'un nœud, ou `window` en secours.
 * @param {Node|Element|Document|null|undefined} s - Le nœud ou document source dont on veut la fenêtre.
 * @returns {Window} La `Window` associée au `document` de `s`, ou l'objet global `window` si elle n'est pas disponible.
 */
function A(s){var t;return(s==null||(t=s.ownerDocument)==null?void 0:t.defaultView)||window}/**
 * Récupère l'élément racine (<html>) du document associé à la cible donnée.
 * @param {any} s - Élément, nœud, document ou objet global dont on veut le document racine.
 * @returns {Element|undefined} L'élément racine du document (`document.documentElement`) ou `undefined` si aucun document n'est disponible.
 */
function P(s){var t;return(t=(Pt(s)?s.ownerDocument:s.document)||window.document)==null?void 0:t.documentElement}/**
 * Détermine si la valeur fournie représente un nœud DOM.
 *
 * Cette vérification tient compte des nœuds provenant d'un autre contexte de document (cross-realm).
 * @param {*} s - La valeur à tester.
 * @returns {boolean} `true` si `s` est un nœud DOM, `false` sinon.
 */
function Pt(s){return ct()?s instanceof Node||s instanceof A(s).Node:!1}/**
 * Détermine si la valeur fournie est un élément DOM valide dans l'environnement d'exécution.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` est une instance de `Element` accessible dans l'environnement DOM actuel, `false` sinon.
 */
function C(s){return ct()?s instanceof Element||s instanceof A(s).Element:!1}/**
 * Indique si la valeur fournie est une instance d'HTMLElement dans l'environnement DOM courant.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si la valeur est une instance d'HTMLElement dans l'environnement DOM courant, `false` sinon.
 */
function T(s){return ct()?s instanceof HTMLElement||s instanceof A(s).HTMLElement:!1}/**
 * Détermine si la valeur fournie représente un ShadowRoot DOM valide.
 *
 * Renvoie `false` si l'API Shadow DOM n'est pas disponible dans l'environnement.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si `s` est un ShadowRoot, `false` sinon.
 */
function Tt(s){return!ct()||typeof ShadowRoot>"u"?!1:s instanceof ShadowRoot||s instanceof A(s).ShadowRoot}var re=new Set(["inline","contents"]);/**
 * Détermine si un élément crée un contexte de débordement basé sur ses propriétés CSS.
 *
 * @param {Element} s - Élément DOM à tester.
 * @returns {boolean} `true` si l'une des propriétés `overflow`, `overflow-x` ou `overflow-y` vaut `auto`, `scroll`, `overlay`, `hidden` ou `clip` et que le `display` de l'élément n'appartient pas à la liste des affichages exclus ; `false` sinon.
 */
function q(s){let{overflow:t,overflowX:e,overflowY:i,display:n}=D(s);return/auto|scroll|overlay|hidden|clip/.test(t+i+e)&&!re.has(n)}var le=new Set(["table","td","th"]);/**
 * Indique si l'entité fournie est enregistrée dans le registre interne de nœuds.
 * @param {any} s - Valeur source (généralement un nœud DOM ou une référence) à tester.
 * @returns {boolean} `true` si l'entité mappée est présente dans le registre, `false` sinon.
 */
function Mt(s){return le.has(K(s))}var ae=[":popover-open",":modal"];/**
 * Détermine si l'élément correspond à au moins un sélecteur de la liste `ae`.
 * Les erreurs lors de l'appel à `matches` sont traitées comme des non-correspondances.
 * @param {Element} s - L'élément à tester.
 * @returns {boolean} `true` si l'élément correspond à au moins un sélecteur de `ae`, `false` sinon.
 */
function nt(s){return ae.some(t=>{try{return s.matches(t)}catch{return!1}})}var ce=["transform","translate","scale","rotate","perspective"],he=["transform","translate","scale","rotate","perspective","filter"],de=["paint","layout","strict","content"];/**
 * Détermine si un élément ou son style calculé possède des propriétés CSS susceptibles d'affecter le rendu (filtres, transformations, `will-change`, `contain`, `container-type`).
 * @param {Element|CSSStyleDeclaration} s - Un élément DOM ou son objet de styles calculés ; si `s` est un élément, ses styles calculés sont utilisés.
 * @returns {boolean} `true` si l'élément/style contient au moins une de ces propriétés actives, `false` sinon.
 */
function ht(s){let t=dt(),e=C(s)?D(s):s;return ce.some(i=>e[i]?e[i]!=="none":!1)||(e.containerType?e.containerType!=="normal":!1)||!t&&(e.backdropFilter?e.backdropFilter!=="none":!1)||!t&&(e.filter?e.filter!=="none":!1)||he.some(i=>(e.willChange||"").includes(i))||de.some(i=>(e.contain||"").includes(i))}/**
 * Recherche l'ancêtre le plus proche d'un nœud correspondant à une condition spécifique.
 *
 * Parcourt les ancêtres en partant du parent de `s` et renvoie le premier ancêtre pour lequel la condition interne `ht` est vraie.
 * La recherche s'arrête et renvoie `null` si un ancêtre satisfait la condition interne `nt` ou si aucun ancêtre valide n'est trouvé.
 *
 * @param {Node|Element} s - Le nœud de départ dont on cherche l'ancêtre.
 * @returns {Node|null} Le premier ancêtre correspondant à la condition, ou `null` si aucun trouvé.
 */
function Bt(s){let t=N(s);for(;T(t)&&!_(t);){if(ht(t))return t;if(nt(t))return null;t=N(t)}return null}/**
 * Détecte la prise en charge de la propriété CSS `-webkit-backdrop-filter`.
 *
 * Renvoie `false` si l'API `CSS.supports` n'est pas disponible dans l'environnement.
 * @returns {boolean} `true` si le navigateur prend en charge `-webkit-backdrop-filter`, `false` sinon.
 */
function dt(){return typeof CSS>"u"||!CSS.supports?!1:CSS.supports("-webkit-backdrop-filter","none")}var fe=new Set(["html","body","#document"]);/**
 * Vérifie si la valeur fournie (généralement un élément ou nœud DOM) est suivie par le cache interne.
 *
 * @param {any} s - Valeur à tester, typiquement un élément ou nœud DOM.
 * @returns {boolean} `true` si la valeur est présente dans le cache interne, `false` sinon.
 */
function _(s){return fe.has(K(s))}/**
 * Récupère l'objet de styles calculés pour un élément DOM.
 * @param {Element} s - L'élément DOM dont on veut obtenir les styles calculés.
 * @returns {CSSStyleDeclaration} L'objet représentant les styles calculés de l'élément.
 */
function D(s){return A(s).getComputedStyle(s)}/**
 * Récupère les positions de défilement horizontale et verticale d'un élément ou de la fenêtre.
 * @param {Element|Window} s - L'élément DOM ou l'objet window à interroger.
 * @returns {{scrollLeft: number, scrollTop: number}} Un objet contenant `scrollLeft` et `scrollTop` en pixels.
 */
function ot(s){return C(s)?{scrollLeft:s.scrollLeft,scrollTop:s.scrollTop}:{scrollLeft:s.scrollX,scrollTop:s.scrollY}}/**
 * Détermine l'élément parent pertinent pour un nœud donné en remontant via slot, parent DOM ou hôte de shadow root.
 *
 * @param {Node|HTMLElement|string} s - Le nœud ou la valeur d'entrée à résoudre.
 * @returns {Node|HTMLElement|string} L'élément résolu : l'entrée `s` si sa nature est `html`, sinon l'élément assigné au slot, le parent DOM, l'hôte du shadow root ou la valeur renvoyée par P(s).
 */
function N(s){if(K(s)==="html")return s;let t=s.assignedSlot||s.parentNode||Tt(s)&&s.host||P(s);return Tt(t)?t.host:t}/**
 * Obtient l'élément offset-parent (contenant de position) le plus proche d'un nœud.
 *
 * Parcourt les ancêtres du nœud pour trouver le premier conteneur utilisé comme contexte de positionnement ; retourne le body du document si le contexte est la fenêtre.
 * @param {Node|Element} s - Le nœud dont on recherche l'offset-parent.
 * @returns {Element} L'élément offset-parent le plus proche, ou le <body> du document si aucun autre conteneur n'est applicable.
 */
function Nt(s){let t=N(s);return _(t)?s.ownerDocument?s.ownerDocument.body:s.body:T(t)&&q(t)?t:Nt(t)}/**
 * Construit la liste des racines/ancêtres pertinents pour un nœud donné (documents, visualViewport et shadow roots).
 *
 * @param {Node} s - Le nœud de départ dont on collecte les racines/ancêtres.
 * @param {Array} [t=[]] - Accumulateur optionnel utilisé pour la récursion.
 * @param {boolean} [e=true] - Indique si les shadow roots doivent être suivies.
 * @returns {Array} Un tableau des nœuds racines/ancêtres rencontrés, dans l'ordre de remontée. */
function at(s,t,e){var i;t===void 0&&(t=[]),e===void 0&&(e=!0);let n=Nt(s),o=n===((i=s.ownerDocument)==null?void 0:i.body),r=A(n);if(o){let l=ft(r);return t.concat(r,r.visualViewport||[],q(n)?n:[],l&&e?at(l):[])}return t.concat(n,at(n,[],e))}/**
 * Retourne l'élément cadre (`frameElement`) du parent si ce parent existe et possède un prototype, sinon retourne `null`.
 * @param {Element|Node} s - Élément dont on veut récupérer le `frameElement` du parent.
 * @returns {Element|null} Le `frameElement` du parent si présent et avec prototype, `null` sinon.
 */
function ft(s){return s.parent&&Object.getPrototypeOf(s.parent)?s.frameElement:null}/**
 * Détermine les dimensions visibles d'un élément en combinant les styles calculés et les dimensions réelles.
 * @param {Element|HTMLElement} s - Élément DOM dont on veut obtenir la largeur et la hauteur.
 * @returns {{width: number, height: number, $: boolean}} Objet contenant `width` et `height` en pixels, et `$` valant `true` si les dimensions réelles (offsetWidth/offsetHeight) ont été utilisées à la place des valeurs calculées.
 */
function $t(s){let t=D(s),e=parseFloat(t.width)||0,i=parseFloat(t.height)||0,n=T(s),o=n?s.offsetWidth:e,r=n?s.offsetHeight:i,l=et(e)!==o||et(i)!==r;return l&&(e=o,i=r),{width:e,height:i,$:l}}/**
 * Renvoie la racine de contexte : retourne `s` si c'est une racine de shadow, sinon son `contextElement`.
 *
 * @param {Element|ShadowRoot|Object} s - Un élément DOM ou un objet de contexte pouvant contenir `contextElement`.
 * @returns {Element|ShadowRoot|Object} `s` lorsque c'est une shadow root, sinon la propriété `s.contextElement`.
 */
function zt(s){return C(s)?s:s.contextElement}/**
 * Calcule les facteurs d'échelle horizontaux et verticaux d'un élément DOM.
 *
 * @param {Element|string|null|undefined} s - Élément DOM, sélecteur ou valeur pouvant être résolue en élément.
 * @returns {{x: number, y: number}} Les facteurs d'échelle : `x` pour l'axe horizontal et `y` pour l'axe vertical. Chaque facteur vaut 1 si l'élément n'existe pas ou si la valeur calculée n'est pas finie.
 */
function X(s){let t=zt(s);if(!T(t))return k(1);let e=t.getBoundingClientRect(),{width:i,height:n,$:o}=$t(t),r=(o?et(e.width):e.width)/i,l=(o?et(e.height):e.height)/n;return(!r||!Number.isFinite(r))&&(r=1),(!l||!Number.isFinite(l))&&(l=1),{x:r,y:l}}var ue=k(0);/**
 * Récupère les décalages du visualViewport pour le document lié à l'élément fourni.
 *
 * @param {Element|Document|Window} s - Élément, document ou fenêtre servant de contexte pour déterminer le document dont provient le visualViewport.
 * @returns {{x: number, y: number}|*} Les coordonnées `{x, y}` correspondant à `visualViewport.offsetLeft` et `visualViewport.offsetTop`, ou `ue` si le visualViewport n'est pas disponible.
 */
function Wt(s){let t=A(s);return!dt()||!t.visualViewport?ue:{x:t.visualViewport.offsetLeft,y:t.visualViewport.offsetTop}}/**
 * Vérifie que le drapeau `t` est actif et que la valeur `e` correspond à la valeur attendue pour `s`.
 * @param {*} s - Source utilisée pour déterminer la valeur attendue.
 * @param {boolean} [t=false] - Indicateur activant la comparaison.
 * @param {*} e - Valeur candidate à comparer avec la valeur attendue de `s`.
 * @returns {boolean} `true` si `t` est `true` et que `e` est strictement égal à la valeur attendue pour `s`, `false` sinon.
 */
function pe(s,t,e){return t===void 0&&(t=!1),!e||t&&e!==A(s)?!1:t}/**
 * Calcule le rectangle de l'élément donné en tenant compte des transformations, des facteurs d'échelle et des conteneurs de défilement.
 *
 * @param {Element} s - Élément cible dont on calcule le rectangle.
 * @param {boolean} [t=false] - Si vrai, applique le facteur d'échelle de l'élément ou de l'élément de référence fourni.
 * @param {boolean} [e=false] - Si vrai, prend en compte les offsets des conteneurs (padding/scroll) lors du calcul.
 * @param {Element|undefined} [i] - Élément de référence optionnel utilisé pour déterminer le facteur d'échelle et les offsets au lieu de l'élément cible.
 * @returns {{width: number, height: number, x: number, y: number}} Objet décrivant la largeur, la hauteur et les coordonnées x/y du rectangle calculé (en pixels).
 */
function rt(s,t,e,i){t===void 0&&(t=!1),e===void 0&&(e=!1);let n=s.getBoundingClientRect(),o=zt(s),r=k(1);t&&(i?C(i)&&(r=X(i)):r=X(s));let l=pe(o,e,i)?Wt(o):k(0),a=(n.left+l.x)/r.x,c=(n.top+l.y)/r.y,h=n.width/r.x,d=n.height/r.y;if(o){let u=A(o),f=i&&C(i)?A(i):i,p=u,m=ft(p);for(;m&&i&&f!==p;){let g=X(m),w=m.getBoundingClientRect(),b=D(m),v=w.left+(m.clientLeft+parseFloat(b.paddingLeft))*g.x,O=w.top+(m.clientTop+parseFloat(b.paddingTop))*g.y;a*=g.x,c*=g.y,h*=g.x,d*=g.y,a+=v,c+=O,p=A(m),m=ft(p)}}return U({width:h,height:d,x:a,y:c})}/**
 * Calcule la coordonnée gauche ajustée par le défilement horizontal.
 * @param {Element|Node} s - Élément de référence utilisé pour déterminer le contexte de défilement.
 * @param {{left:number}|null} [t] - Objet rectangulaire optionnel (doit contenir `left`) dont la valeur `left` sert de base si fourni.
 * @returns {number} La position X (gauche) en pixels, ajustée par le décalage horizontal de défilement. 
 */
function ut(s,t){let e=ot(s).scrollLeft;return t?t.left+e:rt(P(s)).left+e}/**
 * Calcule la position en pixels d'un élément DOM en tenant compte du défilement du conteneur fourni.
 * @param {Element} s - L'élément DOM dont on veut la position.
 * @param {{scrollLeft:number,scrollTop:number}} t - L'objet conteneur qui fournit les offsets de défilement.
 * @returns {{x:number,y:number}} Objet contenant `x` (coordonnée horizontale) et `y` (coordonnée verticale) en pixels. 
 */
function Ut(s,t){let e=s.getBoundingClientRect(),i=e.left+t.scrollLeft-ut(s,e),n=e.top+t.scrollTop;return{x:i,y:n}}/**
 * Convertit et normalise un rectangle client (bounding rect) pour le rendre relatif à l'offsetParent en tenant compte du défilement, des transformations CSS, des échelles et de la stratégie de positionnement.
 *
 * @param {Object} s - Données d'entrée.
 * @param {Element|Object} [s.elements] - Objet contenant au moins la propriété `floating` (élément flottant), utilisé pour détecter les cas spéciaux liés aux éléments flottants.
 * @param {ClientRect|DOMRect} s.rect - Rectangle source (généralement le bounding client rect de l'élément flottant).
 * @param {Element|Window} s.offsetParent - Offset parent utilisé comme référence pour la conversion.
 * @param {'absolute'|'fixed'|string} s.strategy - Stratégie de positionnement ; la valeur `"fixed"` active un traitement spécifique.
 * @returns {{width: number, height: number, x: number, y: number}} Le rectangle converti et normalisé (largeur, hauteur et coordonnées x/y relatives à l'offsetParent).
 */
function me(s){let{elements:t,rect:e,offsetParent:i,strategy:n}=s,o=n==="fixed",r=P(i),l=t?nt(t.floating):!1;if(i===r||l&&o)return e;let a={scrollLeft:0,scrollTop:0},c=k(1),h=k(0),d=T(i);if((d||!d&&!o)&&((K(i)!=="body"||q(r))&&(a=ot(i)),T(i))){let f=rt(i);c=X(i),h.x=f.x+i.clientLeft,h.y=f.y+i.clientTop}let u=r&&!d&&!o?Ut(r,a):k(0);return{width:e.width*c.x,height:e.height*c.y,x:e.x*c.x-a.scrollLeft*c.x+h.x+u.x,y:e.y*c.y-a.scrollTop*c.y+h.y+u.y}}/**
 * Récupère les rectangles de mise en page d'un objet DOM et les retourne sous forme de tableau.
 * @param {Element|Range|ClientRectList} s - L'élément DOM ou la plage dont on souhaite obtenir les rectangles client.
 * @returns {DOMRect[]} Un tableau de DOMRect représentant les boîtes de mise en page fournies par `getClientRects()`.
 */
function ge(s){return Array.from(s.getClientRects())}/**
 * Calcule les dimensions visibles et la position d'un élément par rapport au document en tenant compte des défilements et de la direction RTL.
 * @param {Element} s - Élément DOM dont on veut la largeur, la hauteur et la position relative au corps du document.
 * @returns {{width:number, height:number, x:number, y:number}} Objet contenant la largeur et la hauteur visibles et les coordonnées x/y du coin supérieur gauche de l'élément par rapport au body.
 */
function be(s){let t=P(s),e=ot(s),i=s.ownerDocument.body,n=$(t.scrollWidth,t.clientWidth,i.scrollWidth,i.clientWidth),o=$(t.scrollHeight,t.clientHeight,i.scrollHeight,i.clientHeight),r=-e.scrollLeft+ut(s),l=-e.scrollTop;return D(i).direction==="rtl"&&(r+=$(t.clientWidth,i.clientWidth)-n),{width:n,height:o,x:r,y:l}}var Ft=25;/**
 * Calcule les dimensions et la position du viewport utilisable pour l'élément donné en tenant compte de la VisualViewport, des barres de défilement et du mode de positionnement.
 * @param {Element|Document|Window} s - Élément, document ou fenêtre servant de référence pour le calcul du viewport.
 * @param {'fixed'|'absolute'|string} [t] - Mode de positionnement utilisé par l'élément ; si `"fixed"`, la position de la VisualViewport est prise en compte pour x/y.
 * @returns {{width: number, height: number, x: number, y: number}} Objet contenant la largeur et la hauteur du viewport utilisable, et ses coordonnées x/y relatives (offsetLeft/offsetTop de la VisualViewport si applicable).
 */
function ye(s,t){let e=A(s),i=P(s),n=e.visualViewport,o=i.clientWidth,r=i.clientHeight,l=0,a=0;if(n){o=n.width,r=n.height;let h=dt();(!h||h&&t==="fixed")&&(l=n.offsetLeft,a=n.offsetTop)}let c=ut(i);if(c<=0){let h=i.ownerDocument,d=h.body,u=getComputedStyle(d),f=h.compatMode==="CSS1Compat"&&parseFloat(u.marginLeft)+parseFloat(u.marginRight)||0,p=Math.abs(i.clientWidth-d.clientWidth-f);p<=Ft&&(o-=p)}else c<=Ft&&(o+=c);return{width:o,height:r,x:l,y:a}}var we=new Set(["absolute","fixed"]);/**
 * Calcule la largeur, la hauteur et la position (x, y) d'un élément en tenant compte du facteur d'échelle et de la stratégie de positionnement.
 *
 * @param {Element} s - Élément DOM dont on calcule la boîte client.
 * @param {string} t - Stratégie de positionnement ; utilisez la valeur `"fixed"` pour le positionnement fixé au viewport.
 * @returns {{width: number, height: number, x: number, y: number}} Objet contenant les dimensions et la position en pixels : `width`, `height`, `x`, `y`.
 */
function xe(s,t){let e=rt(s,!0,t==="fixed"),i=e.top+s.clientTop,n=e.left+s.clientLeft,o=T(s)?X(s):k(1),r=s.clientWidth*o.x,l=s.clientHeight*o.y,a=n*o.x,c=i*o.y;return{width:r,height:l,x:a,y:c}}/**
 * Normalise et renvoie un rectangle (x, y, width, height) relatif à l'élément de référence.
 * @param {Element} s - Élément de référence utilisé pour calculer les coordonnées.
 * @param {"viewport"|"document"|Element|{x:number,y:number,width:number,height:number}} t - Source des coordonnées : la chaîne `"viewport"`, la chaîne `"document"`, un élément DOM ou un objet rect contenant `x`, `y`, `width` et `height`.
 * @param {Object} [e] - Options supplémentaires pour le calcul de la position.
 * @returns {{x:number,y:number,width:number,height:number}} Le rectangle normalisé exprimé par rapport à l'élément de référence.
 */
function Vt(s,t,e){let i;if(t==="viewport")i=ye(s,e);else if(t==="document")i=be(P(s));else if(C(t))i=xe(t,e);else{let n=Wt(s);i={x:t.x-n.x,y:t.y-n.y,width:t.width,height:t.height}}return U(i)}/**
 * Vérifie si un élément est contenu dans un autre ou si un ancêtre intermédiaire a un positionnement fixe.
 * @param {Node} s - L'élément de départ à tester.
 * @param {Node} t - L'élément ancêtre cible.
 * @returns {boolean} `true` si `s` est égal à `t`, si `t` est un ancêtre de `s`, ou si un ancêtre entre `s` et `t` (inclus) a une position CSS `fixed`, `false` sinon.
 */
function Kt(s,t){let e=N(s);return e===t||!C(e)||_(e)?!1:D(e).position==="fixed"||Kt(e,t)}/**
 * Calcule et met en cache la liste des ancêtres pertinents pour le positionnement (p. ex. parents de défilement / conteneurs de position), en excluant le <body>.
 *
 * Parcourt la chaîne d'ascendance depuis l'élément fourni pour collecter les conteneurs qui influencent le positionnement/overflow, en tenant compte des éléments avec position fixed, des contextes transformés/containing blocks et d'autres règles de conteneur spécifiques; le résultat est mis en cache dans la map fournie.
 *
 * @param {Element} s - Élément source dont on recherche les ancêtres pertinents.
 * @param {WeakMap<Element, Element[]>} t - Cache (WeakMap) where computed ancestor arrays are stored and retrieved.
 * @returns {Element[]} Tableau des ancêtres pertinents pour le positionnement, sans le <body>.
 */
function ve(s,t){let e=t.get(s);if(e)return e;let i=at(s,[],!1).filter(l=>C(l)&&K(l)!=="body"),n=null,o=D(s).position==="fixed",r=o?N(s):s;for(;C(r)&&!_(r);){let l=D(r),a=ht(r);!a&&l.position==="fixed"&&(n=null),(o?!a&&!n:!a&&l.position==="static"&&!!n&&we.has(n.position)||q(r)&&!a&&Kt(s,r))?i=i.filter(h=>h!==r):n=l,r=N(r)}return t.set(s,i),i}/**
 * Calcule la boîte de découpe (clipping rectangle) d'un élément en fonction d'un ensemble de limites et d'une stratégie de positionnement.
 *
 * @param {Object} s - Options de calcul.
 * @param {Element} s.element - Élément DOM dont on calcule la boîte de découpe.
 * @param {("clippingAncestors"|Array<Element>)} s.boundary - Source des limites : la chaîne "clippingAncestors" pour utiliser les ancêtres de découpe de l'élément, ou un tableau d'éléments servant de frontières.
 * @param {Element|string} s.rootBoundary - Limite racine supplémentaire (par ex. "viewport" ou un élément) utilisée comme frontière de dernier recours.
 * @param {("absolute"|"fixed")} s.strategy - Stratégie de positionnement influençant le calcul des rectangles.
 * @returns {{width: number, height: number, x: number, y: number}} Objet décrivant la largeur, la hauteur et l'origine (x, y) de la boîte de découpe.
 */
function Le(s){let{element:t,boundary:e,rootBoundary:i,strategy:n}=s,r=[...e==="clippingAncestors"?nt(t)?[]:ve(t,this._c):[].concat(e),i],l=r[0],a=r.reduce((c,h)=>{let d=Vt(t,h,n);return c.top=$(d.top,c.top),c.right=tt(d.right,c.right),c.bottom=tt(d.bottom,c.bottom),c.left=$(d.left,c.left),c},Vt(t,l,n));return{width:a.right-a.left,height:a.bottom-a.top,x:a.left,y:a.top}}/**
 * Renvoie un objet contenant la largeur et la hauteur pour l'élément ou le rectangle fourni.
 * @param {Element|ClientRect|DOMRect|{width:number,height:number}} s - Élément DOM ou objet de type rect contenant des dimensions.
 * @returns {{width:number,height:number}} Objet avec les propriétés `width` et `height`.
 */
function Oe(s){let{width:t,height:e}=$t(s);return{width:t,height:e}}/**
 * Calcule la position (x, y) et la taille (width, height) d'un élément par rapport à un conteneur de référence en tenant compte d'une stratégie de positionnement.
 * @param {Element|Node} s - Élément dont on mesure la boîte de contenu.
 * @param {Element|Document} t - Conteneur de référence ou document servant de système de coordonnées.
 * @param {string} e - Stratégie de positionnement ; utiliser `"fixed"` pour forcer le calcul en mode fixe.
 * @returns {{x: number, y: number, width: number, height: number}} Les coordonnées x/y relatives au conteneur et les dimensions de l'élément.
 */
function Ae(s,t,e){let i=T(t),n=P(t),o=e==="fixed",r=rt(s,!0,o,t),l={scrollLeft:0,scrollTop:0},a=k(0);function c(){a.x=ut(n)}if(i||!i&&!o)if((K(t)!=="body"||q(n))&&(l=ot(t)),i){let f=rt(t,!0,o,t);a.x=f.x+t.clientLeft,a.y=f.y+t.clientTop}else n&&c();o&&!i&&n&&c();let h=n&&!i&&!o?Ut(n,l):k(0),d=r.left+l.scrollLeft-a.x-h.x,u=r.top+l.scrollTop-a.y-h.y;return{x:d,y:u,width:r.width,height:r.height}}/**
 * Détermine si un élément utilise le positionnement CSS "static".
 * @param {Element} s - L'élément DOM à tester.
 * @returns {boolean} `true` si la propriété CSS `position` vaut `"static"`, `false` sinon.
 */
function xt(s){return D(s).position==="static"}/**
 * Résout le parent d'offset d'un élément DOM.
 *
 * Si l'élément n'est pas un élément DOM valide ou si son style `position` est `fixed`, renvoie `null`.
 * Si l'argument `mapper` est fourni, renvoie le résultat de `mapper(element)` au lieu du parent d'offset calculé.
 * Si le parent d'offset calculé est égal à la racine de l'élément, renvoie la `body` du document de l'élément.
 *
 * @param {Element} s - L'élément dont on veut connaître le parent d'offset.
 * @param {function(Element): any} [t] - Fonction optionnelle appliquée à l'élément ; son résultat est retourné si fournie.
 * @returns {Element|null} Le parent d'offset résolu, la `body` du document si la racine est rencontrée, ou `null`.
 */
function Ht(s,t){if(!T(s)||D(s).position==="fixed")return null;if(t)return t(s);let e=s.offsetParent;return P(s)===e&&(e=e.ownerDocument.body),e}/**
 * Trouve l'ancêtre contenant pertinent pour le positionnement ou le calcul des limites d'un nœud.
 *
 * Recherche l'ancêtre adapté à utiliser comme conteneur/point de référence pour le positionnement (par exemple pour le clipping, l'offset ou la détection de limites). Si aucun conteneur valide n'est trouvé, renvoie l'élément racine/document correspondant au nœud fourni.
 *
 * @param {Node|Element} s - Le nœud cible dont on cherche le conteneur pertinent.
 * @param {*} [t] - Contexte optionnel influençant la recherche du conteneur (mode ou option de recherche).
 * @returns {Element|Document} L'élément ancêtre à utiliser comme conteneur de positionnement, ou l'élément racine/document si aucun ancêtre approprié n'est trouvé.
 */
function _t(s,t){let e=A(s);if(nt(s))return e;if(!T(s)){let n=N(s);for(;n&&!_(n);){if(C(n)&&!xt(n))return n;n=N(n)}return e}let i=Ht(s,t);for(;i&&Mt(i)&&xt(i);)i=Ht(i,t);return i&&_(i)&&xt(i)&&!ht(i)?e:i||Bt(s)||e}var Se=async function(s){let t=this.getOffsetParent||_t,e=this.getDimensions,i=await e(s.floating);return{reference:Ae(s.reference,await t(s.floating),s.strategy),floating:{x:0,y:0,width:i.width,height:i.height}}};/**
 * Détermine si la direction d'écriture du document associé à l'élément est `rtl`.
 * @param {Element|Node|Document|Window} s - Élément, nœud, document ou fenêtre dont on vérifie la direction.
 * @returns {boolean} `true` si la direction est `"rtl"`, `false` sinon.
 */
function Ce(s){return D(s).direction==="rtl"}var De={convertOffsetParentRelativeRectToViewportRelativeRect:me,getDocumentElement:P,getClippingRect:Le,getOffsetParent:_t,getElementRects:Se,getClientRects:ge,getDimensions:Oe,getScale:X,isElement:C,isRTL:Ce};var Jt=It;var jt=kt,qt=Rt;var Xt=(s,t,e)=>{let i=new Map,n={platform:De,...e},o={...n.platform,_c:i};return Et(s,t,{...n,platform:o})};/**
 * Détecte si une valeur est vide ou composée uniquement d'espaces.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si la valeur est `null` ou `undefined`, une chaîne vide, ou une chaîne ne contenant que des espaces ; `false` sinon.
 */
function F(s){return s==null||s===""||typeof s=="string"&&s.trim()===""}/**
 * Teste la négation d'un prédicat appliqué à la valeur fournie.
 * @param {*} s - Valeur à tester.
 * @returns {boolean} `true` si le prédicat appliqué à `s` renvoie `false`, `false` autrement.
 */
function y(s){return!F(s)}var pt=class{constructor({element:t,options:e,placeholder:i,state:n,canOptionLabelsWrap:o=!0,canSelectPlaceholder:r=!0,initialOptionLabel:l=null,initialOptionLabels:a=null,initialState:c=null,isHtmlAllowed:h=!1,isAutofocused:d=!1,isDisabled:u=!1,isMultiple:f=!1,isSearchable:p=!1,getOptionLabelUsing:m=null,getOptionLabelsUsing:g=null,getOptionsUsing:w=null,getSearchResultsUsing:b=null,hasDynamicOptions:v=!1,hasDynamicSearchResults:O=!0,searchPrompt:x="Search...",searchDebounce:J=1e3,loadingMessage:Y="Loading...",searchingMessage:W="Searching...",noSearchResultsMessage:L="No results found",maxItems:S=null,maxItemsMessage:V="Maximum number of items selected",optionsLimit:G=null,position:Q=null,searchableOptionFields:E=["label"],livewireId:j=null,statePath:R=null,onStateChange:M=()=>{}}){this.element=t,this.options=e,this.originalOptions=JSON.parse(JSON.stringify(e)),this.placeholder=i,this.state=n,this.canOptionLabelsWrap=o,this.canSelectPlaceholder=r,this.initialOptionLabel=l,this.initialOptionLabels=a,this.initialState=c,this.isHtmlAllowed=h,this.isAutofocused=d,this.isDisabled=u,this.isMultiple=f,this.isSearchable=p,this.getOptionLabelUsing=m,this.getOptionLabelsUsing=g,this.getOptionsUsing=w,this.getSearchResultsUsing=b,this.hasDynamicOptions=v,this.hasDynamicSearchResults=O,this.searchPrompt=x,this.searchDebounce=J,this.loadingMessage=Y,this.searchingMessage=W,this.noSearchResultsMessage=L,this.maxItems=S,this.maxItemsMessage=V,this.optionsLimit=G,this.position=Q,this.searchableOptionFields=Array.isArray(E)?E:["label"],this.livewireId=j,this.statePath=R,this.onStateChange=M,this.labelRepository={},this.isOpen=!1,this.selectedIndex=-1,this.searchQuery="",this.searchTimeout=null,this.isSearching=!1,this.selectedDisplayVersion=0,this.render(),this.setUpEventListeners(),this.isAutofocused&&this.selectButton.focus()}populateLabelRepositoryFromOptions(t){if(!(!t||!Array.isArray(t)))for(let e of t)e.options&&Array.isArray(e.options)?this.populateLabelRepositoryFromOptions(e.options):e.value!==void 0&&e.label!==void 0&&(this.labelRepository[e.value]=e.label)}render(){this.populateLabelRepositoryFromOptions(this.options),this.container=document.createElement("div"),this.container.className="fi-select-input-ctn",this.canOptionLabelsWrap||this.container.classList.add("fi-select-input-ctn-option-labels-not-wrapped"),this.container.setAttribute("aria-haspopup","listbox"),this.selectButton=document.createElement("button"),this.selectButton.className="fi-select-input-btn",this.selectButton.type="button",this.selectButton.setAttribute("aria-expanded","false"),this.selectedDisplay=document.createElement("div"),this.selectedDisplay.className="fi-select-input-value-ctn",this.updateSelectedDisplay(),this.selectButton.appendChild(this.selectedDisplay),this.dropdown=document.createElement("div"),this.dropdown.className="fi-dropdown-panel fi-scrollable",this.dropdown.setAttribute("role","listbox"),this.dropdown.setAttribute("tabindex","-1"),this.dropdown.style.display="none",this.dropdownId=`fi-select-input-dropdown-${Math.random().toString(36).substring(2,11)}`,this.dropdown.id=this.dropdownId,this.isMultiple&&this.dropdown.setAttribute("aria-multiselectable","true"),this.isSearchable&&(this.searchContainer=document.createElement("div"),this.searchContainer.className="fi-select-input-search-ctn",this.searchInput=document.createElement("input"),this.searchInput.className="fi-input",this.searchInput.type="text",this.searchInput.placeholder=this.searchPrompt,this.searchInput.setAttribute("aria-label","Search"),this.searchContainer.appendChild(this.searchInput),this.dropdown.appendChild(this.searchContainer),this.searchInput.addEventListener("input",t=>{this.isDisabled||this.handleSearch(t)}),this.searchInput.addEventListener("keydown",t=>{if(!this.isDisabled){if(t.key==="Tab"){t.preventDefault();let e=this.getVisibleOptions();if(e.length===0)return;t.shiftKey?this.selectedIndex=e.length-1:this.selectedIndex=0,e.forEach(i=>{i.classList.remove("fi-selected")}),e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus()}else if(t.key==="ArrowDown"){if(t.preventDefault(),t.stopPropagation(),this.getVisibleOptions().length===0)return;this.selectedIndex=-1,this.searchInput.blur(),this.focusNextOption()}else if(t.key==="ArrowUp"){t.preventDefault(),t.stopPropagation();let e=this.getVisibleOptions();if(e.length===0)return;this.selectedIndex=e.length-1,this.searchInput.blur(),e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus(),e[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",e[this.selectedIndex].id),this.scrollOptionIntoView(e[this.selectedIndex])}else if(t.key==="Enter"){if(t.preventDefault(),t.stopPropagation(),this.isSearching)return;let e=this.getVisibleOptions();if(e.length===0)return;let i=e.find(o=>{let r=o.getAttribute("aria-disabled")==="true",l=o.classList.contains("fi-disabled"),a=o.offsetParent===null;return!(r||l||a)});if(!i)return;let n=i.getAttribute("data-value");if(n===null)return;this.selectOption(n)}}})),this.optionsList=document.createElement("ul"),this.renderOptions(),this.container.appendChild(this.selectButton),this.container.appendChild(this.dropdown),this.element.appendChild(this.container),this.applyDisabledState()}renderOptions(){this.optionsList.innerHTML="";let t=0,e=this.options,i=0,n=!1;this.options.forEach(l=>{l.options&&Array.isArray(l.options)?(i+=l.options.length,n=!0):i++}),n?this.optionsList.className="fi-select-input-options-ctn":i>0&&(this.optionsList.className="fi-dropdown-list");let o=n?null:this.optionsList,r=0;for(let l of e){if(this.optionsLimit&&r>=this.optionsLimit)break;if(l.options&&Array.isArray(l.options)){let a=l.options;if(this.isMultiple&&Array.isArray(this.state)&&this.state.length>0&&(a=l.options.filter(c=>!this.state.includes(c.value))),a.length>0){if(this.optionsLimit){let c=this.optionsLimit-r;c<a.length&&(a=a.slice(0,c))}this.renderOptionGroup(l.label,a),r+=a.length,t+=a.length}}else{if(this.isMultiple&&Array.isArray(this.state)&&this.state.includes(l.value))continue;!o&&n&&(o=document.createElement("ul"),o.className="fi-dropdown-list",this.optionsList.appendChild(o));let a=this.createOptionElement(l.value,l);o.appendChild(a),r++,t++}}t===0?(this.searchQuery?this.showNoResultsMessage():this.isMultiple&&this.isOpen&&!this.isSearchable&&this.closeDropdown(),this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList)):(this.hideLoadingState(),this.optionsList.parentNode!==this.dropdown&&this.dropdown.appendChild(this.optionsList))}renderOptionGroup(t,e){if(e.length===0)return;let i=document.createElement("li");i.className="fi-select-input-option-group";let n=document.createElement("div");n.className="fi-dropdown-header",n.textContent=t;let o=document.createElement("ul");o.className="fi-dropdown-list",e.forEach(r=>{let l=this.createOptionElement(r.value,r);o.appendChild(l)}),i.appendChild(n),i.appendChild(o),this.optionsList.appendChild(i)}createOptionElement(t,e){let i=t,n=e,o=!1;typeof e=="object"&&e!==null&&"label"in e&&"value"in e&&(i=e.value,n=e.label,o=e.isDisabled||!1);let r=document.createElement("li");r.className="fi-dropdown-list-item fi-select-input-option",o&&r.classList.add("fi-disabled");let l=`fi-select-input-option-${Math.random().toString(36).substring(2,11)}`;if(r.id=l,r.setAttribute("role","option"),r.setAttribute("data-value",i),r.setAttribute("tabindex","0"),o&&r.setAttribute("aria-disabled","true"),this.isHtmlAllowed&&typeof n=="string"){let h=document.createElement("div");h.innerHTML=n;let d=h.textContent||h.innerText||n;r.setAttribute("aria-label",d)}let a=this.isMultiple?Array.isArray(this.state)&&this.state.includes(i):this.state===i;r.setAttribute("aria-selected",a?"true":"false"),a&&r.classList.add("fi-selected");let c=document.createElement("span");return this.isHtmlAllowed?c.innerHTML=n:c.textContent=n,r.appendChild(c),o||r.addEventListener("click",h=>{h.preventDefault(),h.stopPropagation(),this.selectOption(i),this.isMultiple&&(this.isSearchable&&this.searchInput?setTimeout(()=>{this.searchInput.focus()},0):setTimeout(()=>{r.focus()},0))}),r}async updateSelectedDisplay(){this.selectedDisplayVersion=this.selectedDisplayVersion+1;let t=this.selectedDisplayVersion,e=document.createDocumentFragment();if(this.isMultiple){if(!Array.isArray(this.state)||this.state.length===0){let n=document.createElement("span");n.textContent=this.placeholder,n.classList.add("fi-select-input-placeholder"),e.appendChild(n)}else{let n=await this.getLabelsForMultipleSelection();if(t!==this.selectedDisplayVersion)return;this.addBadgesForSelectedOptions(n,e)}t===this.selectedDisplayVersion&&(this.selectedDisplay.replaceChildren(e),this.isOpen&&this.positionDropdown());return}if(this.state===null||this.state===""){let n=document.createElement("span");n.textContent=this.placeholder,n.classList.add("fi-select-input-placeholder"),e.appendChild(n),t===this.selectedDisplayVersion&&this.selectedDisplay.replaceChildren(e);return}let i=await this.getLabelForSingleSelection();t===this.selectedDisplayVersion&&(this.addSingleSelectionDisplay(i,e),t===this.selectedDisplayVersion&&this.selectedDisplay.replaceChildren(e))}async getLabelsForMultipleSelection(){let t=this.getSelectedOptionLabels(),e=[];if(Array.isArray(this.state)){for(let n of this.state)if(!y(this.labelRepository[n])){if(y(t[n])){this.labelRepository[n]=t[n];continue}e.push(n.toString())}}if(e.length>0&&y(this.initialOptionLabels)&&JSON.stringify(this.state)===JSON.stringify(this.initialState)){if(Array.isArray(this.initialOptionLabels))for(let n of this.initialOptionLabels)y(n)&&n.value!==void 0&&n.label!==void 0&&e.includes(n.value)&&(this.labelRepository[n.value]=n.label)}else if(e.length>0&&this.getOptionLabelsUsing)try{let n=await this.getOptionLabelsUsing();for(let o of n)y(o)&&o.value!==void 0&&o.label!==void 0&&(this.labelRepository[o.value]=o.label)}catch(n){console.error("Error fetching option labels:",n)}let i=[];if(Array.isArray(this.state))for(let n of this.state)y(this.labelRepository[n])?i.push(this.labelRepository[n]):y(t[n])?i.push(t[n]):i.push(n);return i}createBadgeElement(t,e){let i=document.createElement("span");i.className="fi-badge fi-size-md fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-200",y(t)&&i.setAttribute("data-value",t);let n=document.createElement("span");n.className="fi-badge-label-ctn";let o=document.createElement("span");o.className="fi-badge-label",this.canOptionLabelsWrap&&o.classList.add("fi-wrapped"),this.isHtmlAllowed?o.innerHTML=e:o.textContent=e,n.appendChild(o),i.appendChild(n);let r=this.createRemoveButton(t,e);return i.appendChild(r),i}createRemoveButton(t,e){let i=document.createElement("button");return i.type="button",i.className="fi-badge-delete-btn",i.innerHTML='<svg class="fi-icon fi-size-xs" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon"><path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"></path></svg>',i.setAttribute("aria-label","Remove "+(this.isHtmlAllowed?e.replace(/<[^>]*>/g,""):e)),i.addEventListener("click",n=>{n.stopPropagation(),y(t)&&this.selectOption(t)}),i.addEventListener("keydown",n=>{(n.key===" "||n.key==="Enter")&&(n.preventDefault(),n.stopPropagation(),y(t)&&this.selectOption(t))}),i}addBadgesForSelectedOptions(t,e=this.selectedDisplay){let i=document.createElement("div");i.className="fi-select-input-value-badges-ctn",t.forEach((n,o)=>{let r=Array.isArray(this.state)?this.state[o]:null,l=this.createBadgeElement(r,n);i.appendChild(l)}),e.appendChild(i)}async getLabelForSingleSelection(){let t=this.labelRepository[this.state];if(F(t)&&(t=this.getSelectedOptionLabel(this.state)),F(t)&&y(this.initialOptionLabel)&&this.state===this.initialState)t=this.initialOptionLabel,y(this.state)&&(this.labelRepository[this.state]=t);else if(F(t)&&this.getOptionLabelUsing)try{t=await this.getOptionLabelUsing(),y(t)&&y(this.state)&&(this.labelRepository[this.state]=t)}catch(e){console.error("Error fetching option label:",e),t=this.state}else F(t)&&(t=this.state);return t}addSingleSelectionDisplay(t,e=this.selectedDisplay){let i=document.createElement("span");if(i.className="fi-select-input-value-label",this.isHtmlAllowed?i.innerHTML=t:i.textContent=t,e.appendChild(i),!this.canSelectPlaceholder)return;let n=document.createElement("button");n.type="button",n.className="fi-select-input-value-remove-btn",n.innerHTML='<svg class="fi-icon fi-size-sm" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',n.setAttribute("aria-label","Clear selection"),n.addEventListener("click",o=>{o.stopPropagation(),this.selectOption("")}),n.addEventListener("keydown",o=>{(o.key===" "||o.key==="Enter")&&(o.preventDefault(),o.stopPropagation(),this.selectOption(""))}),e.appendChild(n)}getSelectedOptionLabel(t){if(y(this.labelRepository[t]))return this.labelRepository[t];let e="";for(let i of this.options)if(i.options&&Array.isArray(i.options)){for(let n of i.options)if(n.value===t){e=n.label,this.labelRepository[t]=e;break}}else if(i.value===t){e=i.label,this.labelRepository[t]=e;break}return e}setUpEventListeners(){this.buttonClickListener=()=>{this.toggleDropdown()},this.documentClickListener=t=>{!this.container.contains(t.target)&&this.isOpen&&this.closeDropdown()},this.buttonKeydownListener=t=>{this.isDisabled||this.handleSelectButtonKeydown(t)},this.dropdownKeydownListener=t=>{this.isDisabled||this.isSearchable&&document.activeElement===this.searchInput&&!["Tab","Escape"].includes(t.key)||this.handleDropdownKeydown(t)},this.selectButton.addEventListener("click",this.buttonClickListener),document.addEventListener("click",this.documentClickListener),this.selectButton.addEventListener("keydown",this.buttonKeydownListener),this.dropdown.addEventListener("keydown",this.dropdownKeydownListener),!this.isMultiple&&this.livewireId&&this.statePath&&this.getOptionLabelUsing&&(this.refreshOptionLabelListener=async t=>{if(t.detail.livewireId===this.livewireId&&t.detail.statePath===this.statePath&&y(this.state))try{delete this.labelRepository[this.state];let e=await this.getOptionLabelUsing();y(e)&&(this.labelRepository[this.state]=e);let i=this.selectedDisplay.querySelector(".fi-select-input-value-label");y(i)&&(this.isHtmlAllowed?i.innerHTML=e:i.textContent=e),this.updateOptionLabelInList(this.state,e)}catch(e){console.error("Error refreshing option label:",e)}},window.addEventListener("filament-forms::select.refreshSelectedOptionLabel",this.refreshOptionLabelListener))}updateOptionLabelInList(t,e){this.labelRepository[t]=e;let i=this.getVisibleOptions();for(let n of i)if(n.getAttribute("data-value")===String(t)){if(n.innerHTML="",this.isHtmlAllowed){let o=document.createElement("span");o.innerHTML=e,n.appendChild(o)}else n.appendChild(document.createTextNode(e));break}for(let n of this.options)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===t){o.label=e;break}}else if(n.value===t){n.label=e;break}for(let n of this.originalOptions)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===t){o.label=e;break}}else if(n.value===t){n.label=e;break}}handleSelectButtonKeydown(t){switch(t.key){case"ArrowDown":t.preventDefault(),t.stopPropagation(),this.isOpen?this.focusNextOption():this.openDropdown();break;case"ArrowUp":t.preventDefault(),t.stopPropagation(),this.isOpen?this.focusPreviousOption():this.openDropdown();break;case" ":if(t.preventDefault(),this.isOpen){if(this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}}else this.openDropdown();break;case"Enter":break;case"Escape":this.isOpen&&(t.preventDefault(),this.closeDropdown());break;case"Tab":this.isOpen&&this.closeDropdown();break;default:if(this.isSearchable&&!t.ctrlKey&&!t.metaKey&&!t.altKey&&typeof t.key=="string"&&t.key.length===1){t.preventDefault();let e=t.key;this.isOpen||this.openDropdown(),this.searchInput&&(this.searchInput.focus(),this.searchInput.value=(this.searchInput.value||"")+e,this.searchInput.dispatchEvent(new Event("input",{bubbles:!0})))}break}}handleDropdownKeydown(t){switch(t.key){case"ArrowDown":t.preventDefault(),t.stopPropagation(),this.focusNextOption();break;case"ArrowUp":t.preventDefault(),t.stopPropagation(),this.focusPreviousOption();break;case" ":if(t.preventDefault(),this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}break;case"Enter":if(t.preventDefault(),this.selectedIndex>=0){let e=this.getVisibleOptions()[this.selectedIndex];e&&e.click()}else{let e=this.element.closest("form");e&&e.submit()}break;case"Escape":t.preventDefault(),this.closeDropdown(),this.selectButton.focus();break;case"Tab":this.closeDropdown();break;default:if(this.isSearchable&&!t.ctrlKey&&!t.metaKey&&!t.altKey&&typeof t.key=="string"&&t.key.length===1){t.preventDefault();let e=t.key;this.searchInput&&(this.searchInput.focus(),this.searchInput.value=(this.searchInput.value||"")+e,this.searchInput.dispatchEvent(new Event("input",{bubbles:!0})))}break}}toggleDropdown(){if(!this.isDisabled){if(this.isOpen){this.closeDropdown();return}this.isMultiple&&!this.isSearchable&&!this.hasAvailableOptions()||this.openDropdown()}}hasAvailableOptions(){for(let t of this.options)if(t.options&&Array.isArray(t.options)){for(let e of t.options)if(!Array.isArray(this.state)||!this.state.includes(e.value))return!0}else if(!Array.isArray(this.state)||!this.state.includes(t.value))return!0;return!1}async openDropdown(){this.dropdown.style.display="block",this.dropdown.style.opacity="0";let t=this.selectButton.closest(".fi-fixed-positioning-context")!==null&&this.selectButton.closest(".fi-absolute-positioning-context")===null;if(this.dropdown.style.position=t?"fixed":"absolute",this.dropdown.style.width=`${this.selectButton.offsetWidth}px`,this.selectButton.setAttribute("aria-expanded","true"),this.isOpen=!0,this.positionDropdown(),this.resizeListener||(this.resizeListener=()=>{this.dropdown.style.width=`${this.selectButton.offsetWidth}px`,this.positionDropdown()},window.addEventListener("resize",this.resizeListener)),this.scrollListener||(this.scrollListener=()=>this.positionDropdown(),window.addEventListener("scroll",this.scrollListener,!0)),this.dropdown.style.opacity="1",this.hasDynamicOptions&&this.getOptionsUsing){this.showLoadingState(!1);try{let e=await this.getOptionsUsing(),i=Array.isArray(e)?e:e&&Array.isArray(e.options)?e.options:[];this.options=i,this.originalOptions=JSON.parse(JSON.stringify(i)),this.populateLabelRepositoryFromOptions(i),this.renderOptions()}catch(e){console.error("Error fetching options:",e),this.hideLoadingState()}}if(this.hideLoadingState(),this.isSearchable&&this.searchInput)this.searchInput.value="",this.searchInput.focus(),this.searchQuery="",this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions();else{this.selectedIndex=-1;let e=this.getVisibleOptions();if(this.isMultiple){if(Array.isArray(this.state)&&this.state.length>0){for(let i=0;i<e.length;i++)if(this.state.includes(e[i].getAttribute("data-value"))){this.selectedIndex=i;break}}}else for(let i=0;i<e.length;i++)if(e[i].getAttribute("data-value")===this.state){this.selectedIndex=i;break}this.selectedIndex===-1&&e.length>0&&(this.selectedIndex=0),this.selectedIndex>=0&&(e[this.selectedIndex].classList.add("fi-selected"),e[this.selectedIndex].focus())}}positionDropdown(){let t=this.position==="top"?"top-start":"bottom-start",e=[Jt(4),jt({padding:5})];this.position!=="top"&&this.position!=="bottom"&&e.push(qt());let i=this.selectButton.closest(".fi-fixed-positioning-context")!==null&&this.selectButton.closest(".fi-absolute-positioning-context")===null;Xt(this.selectButton,this.dropdown,{placement:t,middleware:e,strategy:i?"fixed":"absolute"}).then(({x:n,y:o})=>{Object.assign(this.dropdown.style,{left:`${n}px`,top:`${o}px`})})}closeDropdown(){this.dropdown.style.display="none",this.selectButton.setAttribute("aria-expanded","false"),this.isOpen=!1,this.resizeListener&&(window.removeEventListener("resize",this.resizeListener),this.resizeListener=null),this.scrollListener&&(window.removeEventListener("scroll",this.scrollListener,!0),this.scrollListener=null),this.getVisibleOptions().forEach(e=>{e.classList.remove("fi-selected")})}focusNextOption(){let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex>=0&&this.selectedIndex<t.length&&t[this.selectedIndex].classList.remove("fi-selected"),this.selectedIndex===t.length-1&&this.isSearchable&&this.searchInput){this.selectedIndex=-1,this.searchInput.focus(),this.dropdown.removeAttribute("aria-activedescendant");return}this.selectedIndex=(this.selectedIndex+1)%t.length,t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus(),t[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",t[this.selectedIndex].id),this.scrollOptionIntoView(t[this.selectedIndex])}}focusPreviousOption(){let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex>=0&&this.selectedIndex<t.length&&t[this.selectedIndex].classList.remove("fi-selected"),(this.selectedIndex===0||this.selectedIndex===-1)&&this.isSearchable&&this.searchInput){this.selectedIndex=-1,this.searchInput.focus(),this.dropdown.removeAttribute("aria-activedescendant");return}this.selectedIndex=(this.selectedIndex-1+t.length)%t.length,t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus(),t[this.selectedIndex].id&&this.dropdown.setAttribute("aria-activedescendant",t[this.selectedIndex].id),this.scrollOptionIntoView(t[this.selectedIndex])}}scrollOptionIntoView(t){if(!t)return;let e=this.dropdown.getBoundingClientRect(),i=t.getBoundingClientRect();i.bottom>e.bottom?this.dropdown.scrollTop+=i.bottom-e.bottom:i.top<e.top&&(this.dropdown.scrollTop-=e.top-i.top)}getVisibleOptions(){let t=[];this.optionsList.classList.contains("fi-dropdown-list")?t=Array.from(this.optionsList.querySelectorAll(':scope > li[role="option"]')):t=Array.from(this.optionsList.querySelectorAll(':scope > ul.fi-dropdown-list > li[role="option"]'));let e=Array.from(this.optionsList.querySelectorAll('li.fi-select-input-option-group > ul > li[role="option"]'));return[...t,...e]}getSelectedOptionLabels(){if(!Array.isArray(this.state)||this.state.length===0)return{};let t={};for(let e of this.state){let i=!1;for(let n of this.options)if(n.options&&Array.isArray(n.options)){for(let o of n.options)if(o.value===e){t[e]=o.label,i=!0;break}if(i)break}else if(n.value===e){t[e]=n.label,i=!0;break}}return t}handleSearch(t){let e=t.target.value.trim();if(this.searchQuery=e,this.searchTimeout&&clearTimeout(this.searchTimeout),e===""){this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions();return}if(!this.getSearchResultsUsing||typeof this.getSearchResultsUsing!="function"||!this.hasDynamicSearchResults){this.filterOptions(e);return}this.searchTimeout=setTimeout(async()=>{this.searchTimeout=null,this.isSearching=!0;try{this.showLoadingState(!0);let i=await this.getSearchResultsUsing(e),n=Array.isArray(i)?i:i&&Array.isArray(i.options)?i.options:[];this.options=n,this.populateLabelRepositoryFromOptions(n),this.hideLoadingState(),this.renderOptions(),this.isOpen&&this.positionDropdown(),this.options.length===0&&this.showNoResultsMessage()}catch(i){console.error("Error fetching search results:",i),this.hideLoadingState(),this.options=JSON.parse(JSON.stringify(this.originalOptions)),this.renderOptions()}finally{this.isSearching=!1}},this.searchDebounce)}showLoadingState(t=!1){this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList),this.hideLoadingState();let e=document.createElement("div");e.className="fi-select-input-message",e.textContent=t?this.searchingMessage:this.loadingMessage,this.dropdown.appendChild(e)}hideLoadingState(){let t=this.dropdown.querySelector(".fi-select-input-message");t&&t.remove()}showNoResultsMessage(){this.optionsList.parentNode===this.dropdown&&this.dropdown.removeChild(this.optionsList),this.hideLoadingState();let t=document.createElement("div");t.className="fi-select-input-message",t.textContent=this.noSearchResultsMessage,this.dropdown.appendChild(t)}filterOptions(t){let e=this.searchableOptionFields.includes("label"),i=this.searchableOptionFields.includes("value");t=t.toLowerCase();let n=[];for(let o of this.originalOptions)if(o.options&&Array.isArray(o.options)){let r=o.options.filter(l=>e&&l.label.toLowerCase().includes(t)||i&&String(l.value).toLowerCase().includes(t));r.length>0&&n.push({label:o.label,options:r})}else(e&&o.label.toLowerCase().includes(t)||i&&String(o.value).toLowerCase().includes(t))&&n.push(o);this.options=n,this.renderOptions(),this.options.length===0&&this.showNoResultsMessage(),this.isOpen&&this.positionDropdown()}selectOption(t){if(this.isDisabled)return;if(!this.isMultiple){this.state=t,this.updateSelectedDisplay(),this.renderOptions(),this.closeDropdown(),this.selectButton.focus(),this.onStateChange(this.state);return}let e=Array.isArray(this.state)?[...this.state]:[];if(e.includes(t)){let n=this.selectedDisplay.querySelector(`[data-value="${t}"]`);if(y(n)){let o=n.parentElement;y(o)&&o.children.length===1?(e=e.filter(r=>r!==t),this.state=e,this.updateSelectedDisplay()):(n.remove(),e=e.filter(r=>r!==t),this.state=e)}else e=e.filter(o=>o!==t),this.state=e,this.updateSelectedDisplay();this.renderOptions(),this.isOpen&&this.positionDropdown(),this.maintainFocusInMultipleMode(),this.onStateChange(this.state);return}if(this.maxItems&&e.length>=this.maxItems){this.maxItemsMessage&&alert(this.maxItemsMessage);return}e.push(t),this.state=e;let i=this.selectedDisplay.querySelector(".fi-select-input-value-badges-ctn");F(i)?this.updateSelectedDisplay():this.addSingleBadge(t,i),this.renderOptions(),this.isOpen&&this.positionDropdown(),this.maintainFocusInMultipleMode(),this.onStateChange(this.state)}async addSingleBadge(t,e){let i=this.labelRepository[t];if(F(i)&&(i=this.getSelectedOptionLabel(t),y(i)&&(this.labelRepository[t]=i)),F(i)&&this.getOptionLabelsUsing)try{let o=await this.getOptionLabelsUsing();for(let r of o)if(y(r)&&r.value===t&&r.label!==void 0){i=r.label,this.labelRepository[t]=i;break}}catch(o){console.error("Error fetching option label:",o)}F(i)&&(i=t);let n=this.createBadgeElement(t,i);e.appendChild(n)}maintainFocusInMultipleMode(){if(this.isSearchable&&this.searchInput){this.searchInput.focus();return}let t=this.getVisibleOptions();if(t.length!==0){if(this.selectedIndex=-1,Array.isArray(this.state)&&this.state.length>0){for(let e=0;e<t.length;e++)if(this.state.includes(t[e].getAttribute("data-value"))){this.selectedIndex=e;break}}this.selectedIndex===-1&&(this.selectedIndex=0),t[this.selectedIndex].classList.add("fi-selected"),t[this.selectedIndex].focus()}}disable(){this.isDisabled||(this.isDisabled=!0,this.applyDisabledState(),this.isOpen&&this.closeDropdown())}enable(){this.isDisabled&&(this.isDisabled=!1,this.applyDisabledState())}applyDisabledState(){if(this.isDisabled){if(this.selectButton.setAttribute("disabled","disabled"),this.selectButton.setAttribute("aria-disabled","true"),this.selectButton.classList.add("fi-disabled"),this.isMultiple&&this.container.querySelectorAll(".fi-select-input-badge-remove").forEach(e=>{e.setAttribute("disabled","disabled"),e.classList.add("fi-disabled")}),!this.isMultiple&&this.canSelectPlaceholder){let t=this.container.querySelector(".fi-select-input-value-remove-btn");t&&(t.setAttribute("disabled","disabled"),t.classList.add("fi-disabled"))}this.isSearchable&&this.searchInput&&(this.searchInput.setAttribute("disabled","disabled"),this.searchInput.classList.add("fi-disabled"))}else{if(this.selectButton.removeAttribute("disabled"),this.selectButton.removeAttribute("aria-disabled"),this.selectButton.classList.remove("fi-disabled"),this.isMultiple&&this.container.querySelectorAll(".fi-select-input-badge-remove").forEach(e=>{e.removeAttribute("disabled"),e.classList.remove("fi-disabled")}),!this.isMultiple&&this.canSelectPlaceholder){let t=this.container.querySelector(".fi-select-input-value-remove-btn");t&&(t.removeAttribute("disabled"),t.classList.add("fi-disabled"))}this.isSearchable&&this.searchInput&&(this.searchInput.removeAttribute("disabled"),this.searchInput.classList.remove("fi-disabled"))}}destroy(){this.selectButton&&this.buttonClickListener&&this.selectButton.removeEventListener("click",this.buttonClickListener),this.documentClickListener&&document.removeEventListener("click",this.documentClickListener),this.selectButton&&this.buttonKeydownListener&&this.selectButton.removeEventListener("keydown",this.buttonKeydownListener),this.dropdown&&this.dropdownKeydownListener&&this.dropdown.removeEventListener("keydown",this.dropdownKeydownListener),this.resizeListener&&(window.removeEventListener("resize",this.resizeListener),this.resizeListener=null),this.scrollListener&&(window.removeEventListener("scroll",this.scrollListener,!0),this.scrollListener=null),this.refreshOptionLabelListener&&window.removeEventListener("filament-forms::select.refreshSelectedOptionLabel",this.refreshOptionLabelListener),this.isOpen&&this.closeDropdown(),this.searchTimeout&&(clearTimeout(this.searchTimeout),this.searchTimeout=null),this.container&&this.container.remove()}};/**
 * Fournit un objet de composant Alpine.js qui gère un sélecteur enrichi (options, recherche, chargement dynamique et synchronisation serveur).
 *
 * @param {boolean} s - Autorise le retour à la ligne des labels d'option.
 * @param {boolean} t - Autorise l'affichage d'un placeholder sélectionnable.
 * @param {function} e - Fonction qui retourne le libellé affiché pour une option.
 * @param {function|Array} i - Source ou fonction retournant la liste initiale d'options.
 * @param {function} n - Fonction utilisée pour récupérer des résultats de recherche dynamiques.
 * @param {boolean} o - Indique si les options proviennent d'une source dynamique.
 * @param {boolean} r - Indique si les résultats de recherche sont chargés dynamiquement.
 * @param {string} l - Libellé initial affiché quand aucune option n'est sélectionnée.
 * @param {boolean} a - Indique que le sélecteur est désactivé.
 * @param {boolean} c - Autorise le rendu HTML dans les labels d'option.
 * @param {boolean} h - Utiliser le contrôle natif du navigateur au lieu du sélect personnalisé.
 * @param {boolean} d - Active la recherche au sein du sélecteur.
 * @param {string} u - Message affiché pendant le chargement des résultats.
 * @param {string} f - Nom du champ (utilisé pour la synchronisation serveur).
 * @param {string} p - Message affiché quand aucune option ne correspond à la recherche.
 * @param {Array} m - Tableau d'options initiales.
 * @param {number} g - Limite maximale d'options affichées.
 * @param {string} w - Texte du placeholder.
 * @param {string} b - Position du panneau d'options (par ex. 'bottom', 'top').
 * @param {string|number} v - Clé d'enregistrement utilisée pour les mises à jour côté serveur.
 * @param {Array<string>} O - Champs des options à utiliser pour la recherche.
 * @param {number} x - Délai de debounce (en ms) avant d'exécuter la recherche.
 * @param {string} J - Message affiché pendant l'état "recherche en cours".
 * @param {string} Y - Invite ou placeholder spécifique au champ de recherche.
 * @param {*} W - Valeur d'état initiale synchronisée avec le serveur.
 *
 * @returns {Object} Un objet de composant Alpine.js contenant l'état, les références et les méthodes de cycle de vie (init, destroy) pour gérer le sélecteur et la synchronisation avec le backend.
 */
function Ee({canOptionLabelsWrap:s,canSelectPlaceholder:t,getOptionLabelUsing:e,getOptionsUsing:i,getSearchResultsUsing:n,hasDynamicOptions:o,hasDynamicSearchResults:r,initialOptionLabel:l,isDisabled:a,isHtmlAllowed:c,isNative:h,isSearchable:d,loadingMessage:u,name:f,noSearchResultsMessage:p,options:m,optionsLimit:g,placeholder:w,position:b,recordKey:v,searchableOptionFields:O,searchDebounce:x,searchingMessage:J,searchPrompt:Y,state:W}){return{error:void 0,isLoading:!1,select:null,state:W,init(){h||(this.select=new pt({element:this.$refs.select,options:m,placeholder:w,state:this.state,canOptionLabelsWrap:s,canSelectPlaceholder:t,initialOptionLabel:l,isHtmlAllowed:c,isDisabled:a,isSearchable:d,getOptionLabelUsing:e,getOptionsUsing:i,getSearchResultsUsing:n,hasDynamicOptions:o,hasDynamicSearchResults:r,searchPrompt:Y,searchDebounce:x,loadingMessage:u,searchingMessage:J,noSearchResultsMessage:p,optionsLimit:g,position:b,searchableOptionFields:O,onStateChange:L=>{this.state=L}})),Livewire.hook("commit",({component:L,commit:S,succeed:V,fail:G,respond:Q})=>{V(({snapshot:E,effect:j})=>{this.$nextTick(()=>{if(this.isLoading||L.id!==this.$root.closest("[wire\\:id]")?.attributes["wire:id"].value)return;let R=this.getServerState();R===void 0||this.getNormalizedState()===R||(this.state=R)})})}),this.$watch("state",async L=>{!h&&this.select&&this.select.state!==L&&(this.select.state=L,this.select.updateSelectedDisplay(),this.select.renderOptions());let S=this.getServerState();if(S===void 0||this.getNormalizedState()===S)return;this.isLoading=!0;let V=await this.$wire.updateTableColumnState(f,v,this.state);this.error=V?.error??void 0,!this.error&&this.$refs.serverState&&(this.$refs.serverState.value=this.getNormalizedState()),this.isLoading=!1})},getServerState(){if(this.$refs.serverState)return[null,void 0].includes(this.$refs.serverState.value)?"":this.$refs.serverState.value.replaceAll('\\"','"')},getNormalizedState(){let L=Alpine.raw(this.state);return[null,void 0].includes(L)?"":L},destroy(){this.select&&(this.select.destroy(),this.select=null)}}}export{Ee as default};