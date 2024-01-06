# Spending Profile 

Spending Profile is a website I wrote to track finances. I posted the code here out of interest, mainly to explore changes in software development practices since 2009.

You can visit the website <a href="https://www.spendingprofile.com/" target="new">here</a>. There is a live demo (use Chrome or Edge).

## Differences in software development practices since 2009

One big difference is simply the choice programming languages, frameworks and libraries.

* **PHP**. In 2009 I used PHP for everything on the server side, whereas today I would choose Node.js.

* **jQuery**. I was using jQuery to manipulate the DOM in 2009. If I were to re-write this today, I would port the jQuery code to React, Angular or Vue. React in particular has a virtual DOM, making it quick to render and more responsive for the user.

## AJAX

Ajax, or "Asynchronous JavaScript and XML", was the big hot thing in 2009. The idea that you could update any object on a web page *without refreshing the whole  page* was indeed powerful. I was pretty excited about it myself and used it extensively in the Spending Profile project. At the time, the XMLHttpRequest object was quite new and wasn't supported by all browsers. Internet Explorer in particular still required ActiveX controls to achieve the same thing. So my code used both approaches and detected the user's browser to switch between them.

Updating this today, I wouldn't bother at all with ActiveX, nor even XMLHttpRequest. The whole mechanism has been replaced with a built in 'fetch' function to make REST API calls to the server.

## JavaScript

In 2009, JavaScript was a lot less evolved than it is today. For one thing, before ES6 there weren't any classes. But you could fake it in a way, by using functions. It's easy to forget that we didn't have arrow functions either, nor promises, nor the async/await construct.

## World Reach

It was never my intention to profit from Spending Profile, but I did implement a login system with user accounts. I still chuckle to see that people in many parts of the world are still using it. I only really feel this when the domain name or hosting package expires and needs renewing, and everyone gets afraid that I'm shutting it down!




