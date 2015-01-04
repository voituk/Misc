#!/usr/bin/env node

var fs = require('fs'),
	spawn = require('child_process').spawn;

var EXT     = ".markdown";
var PREFIX  = '<!DOCTYPE html><html><title>{{TITLE}}</title><xmp theme="united" style="display:none;">';
var POSTFIX = '</xmp><script src="http://strapdownjs.com/v/0.2/strapdown.js"></script></html>';

var node = process.argv.shift();
var me   = process.argv.shift();

if (process.argv.length <= 0) {
	process.stderr.write("Usage: " + node + " " + me + " <file.markdown>\n\n")
	process.exit(1);
}

var mdFile = process.argv.shift();
if (!fs.existsSync(mdFile)) {
	process.stderr.write("File not found: " + mdFile + "\n");
	process.exit(2);
}


var htmlFile = mdFile.replace(EXT, '.html');
if (fs.existsSync(htmlFile)) {
	process.exit(0);
}

process.stdout.write(mdFile + " \t -> \t " + htmlFile + "\n");

var mdContent = fs.readFileSync(mdFile, {encoding: "utf-8"});

//TODO: process links
var links = new Array();

var mdContent = mdContent.replace(/\[([^\]]+)\]\(([^\)\ ]+)\)/mg, function(match, title, url, position, fullstr) {
	//console.log(arguments);
	if (fs.existsSync(url+EXT)) {
		links.push(url)
		return '['+title+']('+url+'.html)';
	}
	return '['+title+']('+url+')';
});


//console.log(mdContent)

var htmlContent = PREFIX.replace('{{TITLE}}', mdFile.replace(EXT, '')) + "\n" + mdContent + "\n" + POSTFIX;
fs.writeFileSync(htmlFile, htmlContent);


for (var i=0; i<links.length; i++) {
	//console.log(links[i])
	spawn(node, [me, links[i]+EXT], { stdio: "inherit" });
}




process.exit(0);


