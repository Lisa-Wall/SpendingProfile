# Spending Profile 

Spending Profile is a financial website I wrote in 2009. It tracks income and expenses, keeps a budget, stores receipts, and imports transactions from all major banking systems.

You can visit it at <a href="https://www.spendingprofile.com/" target="new">spendingprofile.com</a>. There is a live demo (use Chrome or Edge).

<img src="https://www.spendingprofile.com/styles/image.php?image=sample/mainpagefull.png"
/>

I originally wrote it to track my own spending, and later opened it up to external users. It's still running today and has many users across the world. I don't plan to update it further.

## How it was written

* **PHP**. In 2009 I used PHP for everything on the server side. If I were to write this today, I would use Node.js.

* **jQuery**. I was using jQuery to manipulate the DOM in 2009. The jQuery library used to be widely used, but not any more. Other options now exist, such as Angular, Vue and React.

## JavaScript

In 2009, JavaScript was a lot less evolved than it is today. For one thing, before ES6 there weren't any classes. But you could fake it in a way, by using functions. It's also easy to forget that we didn't have arrow functions either, nor promises, nor the async/await construct, to name a few. The major Javascript update ES6 didn't come out until 2015, 6 years after this project was written.

## AJAX

Ajax, or "Asynchronous JavaScript and XML", was the big hot thing in 2009. The idea that you could update any object on a web page *without refreshing the whole  page* was indeed powerful. I was pretty excited about it myself and used it extensively in the Spending Profile project. At the time, the XMLHttpRequest object was quite new and wasn't supported by all browsers. Internet Explorer in particular still required ActiveX controls to achieve the same thing. So my code used both approaches and detected the user's browser to switch between them.

Updating this today, I wouldn't bother at all with ActiveX, nor even XMLHttpRequest. The whole mechanism has been replaced with a built in 'fetch' function to make REST API calls to the server.

## Graphics

One of the things I had a lot of fun with while writing this website was the graphics. I wanted them to be interactve. So for example, on a pie graph you can click on a pie slice to drill down into it. This was done with detailed image-mapping.

<img src="https://spendingprofile.com/styles/images/SamplePieChart_FlatAndMultiLevel.png" />

## Purpose

It was never my intention to profit from Spending Profile. I started it just for myself, to track my own finances. But after a while I decide to open it up, so I implemented a login system with user accounts. These were free and I didn't advertise. Eventually the web's feelers began to find it, and people began to create accounts. I still chuckle to see that people in many parts of the world are still using it. I only really feel this when the domain name or hosting package expires, and I get emails from people who are worried that I'm shutting it down!


