$(document).ready(function(){
    if(background != ''){
        $("#app").prepend(`<style>.mobile_new_nav{background:transparent}#app{background: rgba(0,0,0,.7);}body{background: url('${background}') center/cover;}</style>`);
    }else{
        $("#app").prepend(`<style>.mobile_new_nav{background:transparent}#app{background: rgba(0,0,0,.7);}body{background: url('/static/images/backgrounds/${Math.floor(Math.random() * (6 - 1 + 1) + 1)}.jpg') center/cover;}</style>`);
    }
})