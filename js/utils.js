class Utils {
    static randInt(min, max) {
        return Math.trunc(Math.random() * (max - min + 1) + min);
    }
    static randRange(range) {
        return Utils.randInt(0, range - 1);
    }

    /**
     * 
     * @param {String} action 
     * @param {Object} data 
     * @param {Function} load 
     * @param {Function} error 
     */
    static send(action, data = {}, load = null, error = null) {
        const XHR = new XMLHttpRequest(), FD  = new FormData();
        FD.append('action', action);
        XHR.addEventListener('load', load);
        XHR.addEventListener('error', error);
        for (let key in data)
            FD.append(key, data[key]);
        XHR.open('POST', '../php/server.php');
        XHR.send(FD);
    }
}