function updateProdVals(id,output,maxc,maxe){
	cspan = document.getElementById("conssp"+id);
	espan = document.getElementById("effsp"+id);	
	var index = document.getElementById("pdd"+id).selectedIndex;
	var pval = parseFloat(document.getElementById("pdd"+id)[index].value);
	pval = pval/100;
	var cc = parseInt(maxc * output);
	var ce = parseInt(maxe * output);
	var nc = parseInt(maxc * pval);
	var ne = parseInt(maxe * pval);
	var ed =0;
	var cd=0;
	if(cc==nc && ce==ne){
		cspan.innerHTML = cc;
		cspan.title="";
		espan.innerHTML = ce;
		espan.title="";
	}else{				
		cd = nc-cc;
		ed = ne-ce;
		cspan.innerHTML = nc + "("+ cc+")";
		cspan.title=((cd>=0)?"+":"") + cd;
		espan.innerHTML = ne + "("+ ce+")";
		espan.title=((ed>=0)?"+":"") + ed;
	}
}

function reloadPage(){
	location.reload(true);
}

function liveCount(seconds,name,dual,reload,first){
	span=document.getElementById(name);
	if(first==1){
		var d = new Date();
		d.setTime(d.getTime()+(seconds*1000));
		span.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();
		if(dual==1){
			spanb=document.getElementById("b" + name);
			spanb.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();
		}
	}
	hours = Math.floor(seconds/3600);
	minutes = Math.floor(seconds/60)%60;
	sec = seconds%60;
	span.innerHTML =  hours + ":" + padString(minutes,"0",2) + ":" + padString(sec,"0",2);
	//span.title = hours + ":" + padString(minutes,"0",2) + ":" + padString(sec,"0",2);
	if (seconds>0){
		 setTimeout('liveCount('+(seconds - 1)+',"'+name+'",'+ dual +','+reload+',0);',1000);
	}else{
		if(reload==1) setTimeout('reloadPage()',10000);
	}

}

function livePercent(seconds,cost,buildrate,progress,name,first){
	var sec = seconds;
	var per;
	if (sec > 600) sec = 0;
	per = (((buildrate*(sec/600))+progress)/cost)*100;
	span=document.getElementById(name);
	span.innerHTML = Math.min(Math.round(per),100);
	if (per<100){
		setTimeout('livePercent('+(sec+1)+','+cost+','+buildrate+','+progress+',"'+name+'",0);',1000);
	}
}

function padString(string,chr,len){
	tempstring = string.toString();
	while(tempstring.length < len) tempstring = chr + tempstring;
	return tempstring;
}

function loadShip(){
	var index = document.getElementById("hullselect").selectedIndex;
	var ship = document.getElementById("hullselect")[index].value;
	document.getElementById("description").innerHTML=desc[ship];
	document.getElementById("size").innerHTML=size[ship];
	document.getElementById("cost").innerHTML=cost[ship];
	document.getElementById("maxweapons").innerHTML=weap[ship];
}

function getPartsSize(){
	var engines = parseInt(document.getElementById("engines").value);
	var fuel = parseInt(document.getElementById("fuel").value);
	var cargo = parseInt(document.getElementById("cargo").value);
	var weapons = parseInt(document.getElementById("weapons").value);
	var shields = parseInt(document.getElementById("shields").value);
	return engines + fuel + cargo + weapons + shields;
}

function validateDesForm(){
	var calcsize = getPartsSize();
	var index = document.getElementById("hullselect").selectedIndex;
	var ship = document.getElementById("hullselect")[index].value;
	var wep = parseInt(document.getElementById("weapons").value);
	var engines = parseInt(document.getElementById("engines").value);
	var fuel = parseInt(document.getElementById("fuel").value);
	var shsz = size[ship];
	var mw = weap[ship];
	if(calcsize > shsz){
		alert("You have overloaded your ship by " + (calcsize -shsz) + ".");
		return false;
	}
	if(calcsize < shsz){
		alert("You have " + (shsz-calcsize) + " more space too fill.");
		return false;
	}
	if(wep > mw){
		alert("You have too many weapons. You need to remove " + (wep - mw) + ".");
		return false;
	}
	if(engines<1){
		alert("You need at least 1 engine.");
		return false;
	}
	if(fuel<1){
		alert("You need at least 1 Fuel.");
		return false;
	}
	if((calcsize == shsz) && (wep<=mw)) return true;
	return false;	
}