vcl 4.1;

backend default {
    .host = "web";
    .port = "80";
}

acl purge {
    "web";
} 

sub vcl_recv {
    if (req.method == "PURGE") {
        if (req.http.X-Magento-Tags-Pattern) {
            return (purge);
        } else {
            return (synth(405, "Not allowed."));
        }
    }
}

sub vcl_backend_response {
    set beresp.ttl = 2m;
    set beresp.grace = 6h;
}
