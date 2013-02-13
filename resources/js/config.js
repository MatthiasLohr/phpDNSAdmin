var Config = {
	apiUrl: "api/"
};

/*
 * Error
 */
var Error = {    
    createError: function (msg, error, location) {
        return {
            msg: msg,
            error: error,
            location: location
        };
    }
};