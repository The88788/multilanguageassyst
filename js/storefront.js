// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
(function(){
    'use strict';
    var data=document.getElementById('multilanguageassyst-ui-dictionary');if(!data){return;}
    var dictionary;try{dictionary=JSON.parse(data.textContent);}catch(e){return;}
    var queued=false;
    function translated(text){var arrow=text.match(/^\s*[←→]\s*/);if(arrow){return arrow[0]+translated(text.slice(arrow[0].length));}if(/\s*\($/.test(text)){var base=text.replace(/\s*\($/,""),name=base.trim();if(Object.prototype.hasOwnProperty.call(dictionary,name)){return text.replace(name,dictionary[name]);}}var key=text.trim();if(!Object.prototype.hasOwnProperty.call(dictionary,key)&&/[:：*]$/.test(key)){var plain=key.slice(0,-1).trim();if(Object.prototype.hasOwnProperty.call(dictionary,plain)){return text.replace(plain,dictionary[plain]);}}return Object.prototype.hasOwnProperty.call(dictionary,key)?text.replace(key,dictionary[key]):text;}
    function update(){
        queued=false;
        document.querySelectorAll('#js-order-form,#wa-order-form-wrapper,#wa-order-cart-wrapper,.wa-dialog,.addCart__button,.wa-sku,#checkout .s-step-content,.wa-services,.shop-sk-callback,[data-multilanguageassyst-ui],#wa-my-info-wrapper .wa-name,#wa-my-info-wrapper input[type=button],#wa-my-info-wrapper input[type=submit],#wa-my-info-cancel,#wa-my-info-read-wrapper a[href="?logout"]').forEach(function(root){
            var walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT),nodes=[],node;
            while((node=walker.nextNode())){if(!node.parentElement.closest('script,style,textarea,input,[contenteditable],.wa-product-name,.wa-contact-name,[data-multilanguageassyst-personal]')){nodes.push(node);}}
            nodes.forEach(function(node){var value=translated(node.nodeValue);if(node.parentElement.closest('[data-multilanguageassyst-date]')){value=value.replace(/^(\s*)(Сегодня|Вчера|Завтра|Today|Yesterday|Tomorrow)(?=[\s,]|$)/,function(match,space,label){return space+(dictionary[label]||label);});}if(value!==node.nodeValue){node.nodeValue=value;}});
            var controls=Array.from(root.querySelectorAll('[placeholder],[title],input[type=submit],input[type=button]'));if(root.matches('[placeholder],[title],input[type=submit],input[type=button]')){controls.push(root);}controls.forEach(function(element){
                ['placeholder','title'].forEach(function(name){if(element.hasAttribute(name)){var text=element.getAttribute(name),value=translated(text);if(value!==text){element.setAttribute(name,value);}}});
                if(element.matches('input[type=submit],input[type=button]')){var text=element.value,value=translated(text);if(value!==text){element.value=value;}}
            });
        });
    }
    function schedule(){if(!queued){queued=true;requestAnimationFrame(update);}}
    function start(){update();new MutationObserver(schedule).observe(document.body,{subtree:true,childList:true,characterData:true});}
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',start);}else{start();}
})();
