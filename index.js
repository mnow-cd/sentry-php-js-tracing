import express from 'express';
import * as Sentry from '@sentry/node';

const app = express();
const port = 3000;

app.get('/', (req, res) => {
    console.log(req.headers);

    res.json({
        requestHeaders: {
            traceparent: req.headers['traceparent'],
            'sentry-trace': req.headers['sentry-trace'],
            baggage: req.headers['baggage'],
        },
        sentryTraceData: Sentry.getTraceData(),
    });
});

app.listen(port, () => {
    console.log(`App listening on port ${port}`)
});
