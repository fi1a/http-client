const express = require('express'),
    fs = require('fs'),
    https = require('https'),
    app = express(),
    port = process.argv.length > 2 ? Number(process.argv[2]) : 3000,
    urlencodedParser = express.urlencoded({extended: false});

const options = {
    key: fs.readFileSync(__dirname + '/ssl/key.key').toString(),
    cert: fs.readFileSync(__dirname + '/ssl/cert.pem').toString(),
};


https.createServer(options, app).listen(port, () => {
    console.log(`App listening on port ${port}`)
});

app.get('/200-ok-text-plain', (req, res) => {
    res.status(200).setHeader('Content-Type', 'text/plain').send('success');
});

app.get('/200-ok-json', (req, res) => {
    res.status(200).json({'foo' : 'bar'});
});

app.post('/200-ok-post', urlencodedParser, (req, res) => {
    res.status(200).json({'foo' : req.body.foo});
});

app.get('/200-ok-null-content-length', (req, res) => {
    res.status(200).send(new Array(1000001).join('r'));
});