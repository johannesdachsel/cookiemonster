function setCookieMonster(){
	document.cookie = "cmnstr=1; expires=Fri, 31 Dec 9999 23:59:59 GMT";
	var cmnstrBanner = document.querySelector(".cmnstr");
	cmnstrBanner.parentNode.removeChild(cmnstrBanner);
}