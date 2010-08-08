// Multithreading emulation on node.js in 140 chars (Created for twitter :)
M=Math;for(i=0;i++<3;)(function(x,n){console.log(n+": "+x);if(x<5)setTimeout(arguments.callee,M.round(M.random()*1000),x+1,n)})(0,"Work"+i)
