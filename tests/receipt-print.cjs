// Unit-check print commands without sending jobs to a physical printer.
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
let prints = 0, thermal = false;
const buttons = ['a4', 'thermal'].map(format => ({dataset:{print:format},addEventListener(event,callback){this.click=callback;}}));
const pageStyle = {textContent:''};
const document = {querySelectorAll:()=>buttons,images:[],getElementById:()=>pageStyle,body:{classList:{toggle:(name,value)=>{thermal=value;}}}};
vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/admin/wtbm_receipt.js','utf8'),{document,window:{print:()=>prints++},Promise,Array});
(async()=>{
    buttons[0].click(); await Promise.resolve(); await Promise.resolve();
    assert.equal(thermal,false); assert.match(pageStyle.textContent,/size:A4/); assert.equal(prints,1);
    buttons[1].click(); await Promise.resolve(); await Promise.resolve();
    assert.equal(thermal,true); assert.match(pageStyle.textContent,/margin:4mm/); assert.equal(prints,2);
    console.log('PASS A4 and thermal controls apply their print settings before opening the print dialog.');
})().catch(error=>{console.error(error);process.exitCode=1;});
