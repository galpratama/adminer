<?php

/**
 * Copy data with right click
 * ===================================
 * @author Adil YILDIZ, https://github.com/adilyildiz/AdminerCopy
 * @license MIT
 *
 */
 
class AdminerCopy
{
	
	public function head()
	{
		
		?>
		<menu id="ctxMenu" title="Copy">
			<!--
			<menu title="Sub Menü"></menu>
			-->
		</menu>
		<style>
		#ctxMenu{
    display:none;
    z-index:100;
}
menu {
    position:absolute;
    display:block;
    left:0px;
    top:0px;
    height:20px;
    width:100px;
    padding:0;
    margin:0;
    border:1px solid;
    background-color:white;
    font-weight:normal;
    white-space:nowrap;
}
menu:hover{
    background-color:#eef;
    font-weight:bold;
}
menu:hover > menu{
    display:block;
}
menu > menu{
    display:none;
    position:relative;
    top:-20px;
    left:100%;
    width:55px;
}
menu[title]:before{
    content:attr(title);
}
menu:not([title]):before{
    content:"\2630";
}
</style>
		
		<script <?php echo nonce()?> type='text/javascript'>
		function makeCopiable()
		{
			for (let span of document.querySelectorAll('table' ) )
			{	
				span.addEventListener('contextmenu', headerSpanRightClick);
				span.addEventListener('click', headerSpanClick);
			}
			for (let span of document.querySelectorAll('.jush-sql_code' ) )
			{	
				span.addEventListener('contextmenu', headerSpanRightClick);
				span.addEventListener('click', headerSpanClick);
			}
			for (let span of document.querySelectorAll('#content > h2' ) )
			{	
				span.addEventListener('contextmenu', headerSpanRightClick);
				span.addEventListener('click', headerSpanClick);
			}	
			for (let span of document.querySelectorAll('#tables > li > a.structure' ) )
			{	
				span.addEventListener('contextmenu', headerSpanRightClick);
				span.addEventListener('click', headerSpanClick);
			}		
		}
		
		function CopyToClipboard(str) {
			const el = document.createElement('textarea');
			  el.value = str;
			  document.body.appendChild(el);
			  el.select();
			  document.execCommand('copy');
			  document.body.removeChild(el);
		}
		
		document.addEventListener('DOMContentLoaded', makeCopiable);
		//react to click on Heading while holding Alt-Key.
		var hedef;
		function headerSpanRightClick(e)
		{
			event.preventDefault();
			var ctxMenu = document.getElementById("ctxMenu");
			ctxMenu.style.display = "block";
			ctxMenu.style.left = (event.pageX)+"px";
			ctxMenu.style.top = (event.pageY)+"px";
			hedef=event.target;
		}
		
		function headerSpanClick(e)
		{			
			var ctxMenu = document.getElementById("ctxMenu");
			ctxMenu.style.display = "";
			ctxMenu.style.left = "";
			ctxMenu.style.top = "";				
		}
		var kopyalalink = document.getElementById("ctxMenu");
		kopyalalink.addEventListener("click",function(event){
			if(hedef.tagName=="H2"){
			 bas=hedef.innerText.search(": ");
			 CopyToClipboard(hedef.innerText.substr(bas+2));
			}else{
			CopyToClipboard(hedef.innerText);	
			}
			var ctxMenu = document.getElementById("ctxMenu");
			ctxMenu.style.display = "";
			ctxMenu.style.left = "";
			ctxMenu.style.top = "";
		},false);
		</script>
		<?php
	}
}
