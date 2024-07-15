$(document).ready(function(){
    $("#app").prepend(`<style>#app{background: rgba(0,0,0,.7);}body{background: url('/static/images/backgrounds/${Math.floor(Math.random() * (6 - 1 + 1) + 1)}.jpg') center/cover;}</style>`);
})