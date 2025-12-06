// resources/js/core/Network.js

export default class Network {
    static async request(url, { method = "GET", data = null, headers = {} } = {}) {
        const options = {
            method,
            headers: {
                "Accept": "application/json",
                ...headers
            }
        };

        if (data !== null) {
            options.headers["Content-Type"] = "application/json";
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);

        let json = null;
        try {
            json = await response.json();
        } catch (e) {
            throw new Error("Invalid JSON response");
        }

        if (!response.ok) {
            const error = new Error(json.message || `HTTP ${response.status}`);
            error.status = response.status;
            error.payload = json;
            throw error;
        }

        return json;
    }

    static get(url, params = null) {
        let fullUrl = url;

        if (params && typeof params === "object") {
            const qs = new URLSearchParams(params).toString();
            if (qs) {
                fullUrl += (url.includes("?") ? "&" : "?") + qs;
            }
        }

        return this.request(fullUrl, { method: "GET" });
    }

    static post(url, data = null) {
        return this.request(url, { method: "POST", data });
    }
}
