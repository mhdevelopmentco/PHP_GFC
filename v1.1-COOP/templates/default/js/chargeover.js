var ChargeOver=ChargeOver||{};ChargeOver.Core={CODE_OK:200,settings:{},setup:function(init)
{this.settings=init;if(!('token'in this.settings)||!('instance'in this.settings))
{alert('You must define a ChargeOver "token" and "instance" value.');}
    if(!('extpath'in this.settings))
    {this.settings.extpath='';}
    if(!('scheme'in this.settings))
    {this.settings.scheme='https';}
    if(this.settings.plugins)
    {var scripts=document.getElementsByTagName('script');var origin=scripts[scripts.length-2].src;var split=this.settings.plugins.split(',');for(var i=0;i<split.length;i++)
    {var gateway_src=origin.replace('chargeover.js','plugin-'+split[i]+'.js');var imported=document.createElement('script');imported.src=gateway_src;document.head.appendChild(imported);}
        if(!this.settings.plugins_config)
        {this.settings.plugins_config={};}}},pluginconfig:function(plugin)
{if(this.settings.plugins_config[plugin])
{return this.settings.plugins_config[plugin];}
    return{};},baseurl:function()
{return this.settings.scheme+'://'+this.settings.instance+this.settings.extpath+'/';},funcname:function(func)
{var ret=func.toString();ret=ret.substr('function '.length);ret=ret.substr(0,ret.indexOf('('));return ret;},makefunc:function(callback)
{var name='chargeover_f'+Math.floor(Math.random()*1000001)+Math.floor(Math.random()*1000001);window[name]=function(arg1,arg2,arg3,arg4,arg5){callback(arg1,arg2,arg3,arg4,arg5);}
    return name;},jsonp:function(action,vars,callback)
{var url=ChargeOver.Core.baseurl()+'/?stage=jsonp/endpoint';var str='&jsonp='+this.makefunc(callback)+'&action='+action+'&token='+this.settings.token;vars=this.pack(vars);str+='&payload='+encodeURIComponent(JSON.stringify(vars));url+=str;var head=document.getElementsByTagName('head')[0];var script=document.createElement('script');script.type='text/javascript';script.setAttribute('data-cfasync','false');script.src=url;head.appendChild(script);},pack:function(data)
{return data;},randomtoken:function(length)
{var out='';var chars='abcdefghijklmnopqrstuvwxyz0123456789';for(var i=0;i<length;i++)
{out+=chars.charAt(Math.floor(Math.random()*chars.length));}
    return out;}};;var ChargeOver=ChargeOver||{};ChargeOver.ACH={ACTION_TOKENIZE:'ACH.tokenize',ACTION_GET:'ACH.get',tokenize:function(ach,callback)
{ChargeOver.Core.jsonp(ChargeOver.ACH.ACTION_TOKENIZE,ach,callback);return true;},get:function(token,callback)
{ChargeOver.Core.jsonp(ChargeOver.ACH.ACTION_GET,token,callback);}};;var ChargeOver=ChargeOver||{};ChargeOver.CreditCard={ACTION_TOKENIZE:'CreditCard.tokenize',ACTION_VALIDATE:'CreditCard.validate',ACTION_TYPE:'CreditCard.type',ACTION_GET:'CreditCard.get',tokenize:function(creditcard,callback)
{if(typeof chargeover_creditcard_cse==='undefined')
{}
else
{var config=ChargeOver.Core.pluginconfig('adyen');creditcard=chargeover_creditcard_cse(config,creditcard);}
    ChargeOver.Core.jsonp(ChargeOver.CreditCard.ACTION_TOKENIZE,creditcard,callback);return true;},validate:function(creditcard,callback)
{ChargeOver.Core.jsonp(ChargeOver.CreditCard.ACTION_VALIDATE,creditcard,callback);return true;},type:function(creditcard,callback)
{ChargeOver.Core.jsonp(ChargeOver.CreditCard.ACTION_TYPE,creditcard,callback);return true;},get:function(token,callback)
{ChargeOver.Core.jsonp(ChargeOver.CreditCard.ACTION_GET,token,callback);}};;var ChargeOver=ChargeOver||{};ChargeOver.Customer={ACTION_CREATE:'Customer.create',create:function(creditcard,callback)
{ChargeOver.Core.jsonp(ChargeOver.Customer.ACTION_CREATE,creditcard,callback);return true;}};;var ChargeOver=ChargeOver||{};ChargeOver.Signup={ACTION_SIGNUP:'Signup.signup',signup:function(data,callback)
{ChargeOver.Core.jsonp(ChargeOver.Signup.ACTION_SIGNUP,data,callback);return true;}};