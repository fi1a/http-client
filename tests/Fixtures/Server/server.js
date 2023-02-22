const express = require('express'),
    compress = require('compression'),
    cookieParser = require('cookie-parser'),
    basicAuth = require('basic-auth'),
    passport = require('passport'),
    BearerStrategy = require('passport-http-bearer').Strategy,
    fileUpload = require('express-fileupload'),
    fs = require('fs'),
    https = require('https'),
    http = require('http'),
    app = express(),
    httpsPort = process.argv.length > 2 ? Number(process.argv[2]) : 3000,
    httpPort = process.argv.length > 2 ? Number(process.argv[3]) : 3001,
    urlencodedParser = express.urlencoded({extended: false}),
    oneYear = 1 * 365 * 24 * 60 * 60 * 1000;

let retry = 0;

const options = {
    key: fs.readFileSync(__dirname + '/ssl/key.key').toString(),
    cert: fs.readFileSync(__dirname + '/ssl/cert.pem').toString(),
};

const auth = {name: 'test', pass: 'test'}

app.use(cookieParser());
app.use(compress({threshold: 0}));
app.use(express.static(__dirname + '/public', {maxAge: oneYear}));
passport.use(new BearerStrategy(
    function(token, done){
        if (token === '123') {
            const user = {
                success: true
            }

            return done(null, user);
        }

        return done(null, false);
    }
));
app.use(fileUpload());

app.get('/200-ok-text-plain/', (req, res) => {
    res.status(200);
    res.setHeader('Content-Type', 'text/plain');
    res.send('success');
});

app.get('/200-ok-json/', (req, res) => {
    res.status(200).json({'foo' : req.query.foo});
});

app.post('/200-ok-post/', urlencodedParser, (req, res) => {
    res.status(200).json({'foo' : req.body.foo});
});

app.put('/200-ok-put/', urlencodedParser, (req, res) => {
    res.status(200).json({'foo' : req.body.foo});
});

app.patch('/200-ok-patch/', urlencodedParser, (req, res) => {
    res.status(200).json({'foo' : req.body.foo});
});

app.delete('/200-ok-delete/', (req, res) => {
    res.status(200).json({'foo' : req.query.foo});
});

app.head('/200-ok-head/', (req, res) => {
    res.status(200).send('success');
});

app.options('/200-ok-options/', (req, res) => {
    res.status(200).setHeader('Content-Type', 'text/plain');
    res.send('success');
});

app.get('/200-ok-null-content-length/', (req, res) => {
    res.status(200).send(new Array(1000001).join('r'));
});

app.get('/redirect/', (req, res) => {
    res.redirect('/200-ok-text-plain/');
});

app.get('/redirect-loop/', (req, res) => {
    res.redirect('/redirect-loop/');
});

app.get('/cookie/', (req, res, next) => {
    let cookie = req.cookies.cookieName1,
        value = 1;

    if (cookie) {
    }

    res.cookie('cookieName1', value, {maxAge: 900000, httpOnly: true});
    res.cookie('cookieName2', 'value2=value2', {maxAge: 100000, httpOnly: false, secure: true, domain: '127.0.0.1'});
    res.status(200);
    next();
});

app.get('/cookie-empty/', (req, res, next) => {
    res.status(200).setHeader('Set-Cookie', ' ');
    next();
});

app.get('/cookie-path/', (req, res, next) => {
    res.status(200).setHeader('Set-Cookie', 'cookieName3=value3; Path=path');
    next();
});

app.get('/send-cookie/', (req, res, next) => {
    let cookie = req.cookies.cookieName1;

    res.cookie('cookieName3', cookie);
    res.status(200);
    next();
});

app.get('/basic-auth/', (req, res, next) => {
    const credentials = basicAuth(req);

    if (!credentials || credentials.name !== auth.name || credentials.pass !== auth.pass) {
        res.status(401).send('Access denied');
        next();

        return;
    }

    res.status(200).send('Access granted');
    next();
});

app.get('/bearer-auth/', passport.authenticate('bearer', { session: false }), (req, res, next) => {
    if (req.user.success) {
        res.status(200).send('Access granted');
    }
    next();
});

app.get('/api-key-auth/', (req, res, next) => {
    let token = req.header('token');
    if (req.query.token) {
        token = req.query.token;
    }

    if (token === '123') {
        res.status(200).send('Access granted');
        next();

        return;
    }

    res.status(401).send('Access denied');
    next();
});

app.get('/retry-success/', (req, res, next) => {
    if (retry % 2 === 0) {
        res.status(401).send('Access denied');
        retry++;

        next();
        return;
    }

    retry++;
    res.status(200).send('Access granted');
    next();
});

app.get('/retry-not-success/', (req, res, next) => {
    res.status(401).send('Access denied');
    next();
});

app.post('/file-upload/', urlencodedParser, (req, res, next) => {
    if (!req.files || Object.keys(req.files).length === 0) {
        return res.status(400).send('No files were uploaded.');
    }

    let file1 = req.files.file1,
        file2 = req.files.file2;

    res.status(200).send(req.body.foo + '_' + file1.data.toString('utf8') + '_' + file2.data.toString('utf8'));
    next();
});

app.get('/encoding/', (req, res, next) => {
    res.status(200);
    res.setHeader('Content-Encoding', 'unknown');
    res.send('success');
    next();
});

https.createServer(options, app).listen(httpsPort, () => {
    console.log(`App listening on port ${httpsPort}`)
});

http.createServer(options, app).listen(httpPort, () => {
    console.log(`App listening on port ${httpPort}`)
});