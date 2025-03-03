/*
 * JavaScript client-side example using jsrsasign
 */

// #########################################################
// #             WARNING   WARNING   WARNING               #
// #########################################################
// #                                                       #
// # This file is intended for demonstration purposes      #
// # only.                                                 #
// #                                                       #
// # It is the SOLE responsibility of YOU, the programmer  #
// # to prevent against unauthorized access to any signing #
// # functions.                                            #
// #                                                       #
// # Organizations that do not protect against un-         #
// # authorized signing will be black-listed to prevent    #
// # software piracy.                                      #
// #                                                       #
// # -QZ Industries, LLC                                   #
// #                                                       #
// #########################################################

/**
 * Depends:
 *     - jsrsasign-latest-all-min.js
 *     - qz-tray.js
 *
 * Steps:
 *
 *     1. Include jsrsasign 10.9.0 into your web page
 *        <script src="https://cdnjs.cloudflare.com/ajax/libs/jsrsasign/11.1.0/jsrsasign-all-min.js"></script>
 *
 *     2. Update the privateKey below with contents from private-key.pem
 *
 *     3. Include this script into your web page
 *        <script src="path/to/sign-message.js"></script>
 *
 *     4. Remove or comment out any other references to "setSignaturePromise"
 *
 *     5. IMPORTANT: Before deploying to production, copy "jsrsasign-all-min.js"
 *        to the web server.  Don't trust the CDN above to be available.
 */
var privateKey = "-----BEGIN PRIVATE KEY-----\n" +
"MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCsSwGNL66JvW1g\n" +
"9ns7NqIlc2RU6jLBB5q1Z8jag+zb4Zdx3JxjtX4LZGjj8oQLGkZm+UB5lrXb12CP\n" +
"hoPQAjA3AMtWXH8+HSoDwEBIMJBEQmXwqIWzU3FEkuVwd++znpih9600dBbIssPP\n" +
"LOsPHFMbDsGOkF1ERj2cj6GOoUh5sBMpNs0kZeb76IwBC9c0gt7f8jW2hbYBnQel\n" +
"y7OLNv+tDRuDaqnQDaayVPUIiNxOtrD2EF39bG8/uu4E2Xc0NvekxLU8sv5FjABh\n" +
"HpL/v5V9Wnrt6MuW78AwmSJrqTbXbOdIX7vVnT9zaLsM8gLvLKRAaX55iUtXwoV3\n" +
"fZAvAzA5AgMBAAECggEAPmTdV3lkjloswbgRUZTL7J8Yvw2fOrtbwgUnPkcXYFuW\n" +
"gjP/6LYBwvNmbuJVAkZOJ51tRXsYOdiKDgALPSmFt22QtaJCrEZYgEo/nUUFAcs/\n" +
"6oLFBHeb5dcCwxOUGWq+YK12qq+9zom6koz0RSEfUYWq/8t1EzQSvN9hBOuQeQx7\n" +
"U+RE1X5OVV3cmJzpaeCjGn51KCqmRzWRAhi9xzhXhEC+CkpbEEM2v+xa7+3jASo6\n" +
"iCt+QnRGHPj0DfVB36VM1zGhbpqM465Tb32Qlm8JVs5yKITzJ1YpG1NwOHSnzLFz\n" +
"upykwzEfUDER6Ca4SuWFJh5j8BmXorrdgVmSYA57JwKBgQDnpC+iQn599yyamP4K\n" +
"NRQ7j981qPWO/TTSfS4rz2uMM6ZADFSiDIm/ZZiEUW8YSYityFsI6HfjU4Umlgz1\n" +
"Z3mvCknf/I5vNt7HgfePrktSeuKhJ2q3LOZk4vLO1yrg7V/1vpcLfgw9QYoR6KFR\n" +
"GlIrSC8WfzrgzCkwo9UtLp4n+wKBgQC+aSegepF+IY8OPSf5SgfZSAV/1U2YY76k\n" +
"d7LnpwTMzndc3qiZGrcZ6eS2haLMICz5jB8s0Q9E7dfvy++4n3YgJ4NJyOS8Sw0q\n" +
"kiMfpLU8gHCfyE2vO/r+wBqpw83d3kIduWQqe+3EOox47JBqiYc/z5AWcBEkKmsi\n" +
"yUJAvK3OWwKBgQDYXXz8w71hLaJlGSiZIjEu3Vjxx0ru78YKIlEhLNJZF9lfE+Vt\n" +
"o+Z6d4w5c9MgTXc0U+Pscy+gj9/cReX+x1Na03JjnITyjr8SfWxesb+7X/sV6jp8\n" +
"tJzTeUSxPTvY8wBiC08MtgT6pqAv0Cn2Fm7P7NHG4rp9RVXoD+/MDzI9RwKBgB2k\n" +
"y1mNm3cSjG4AEWgbIWuvbcPAFouCA90hEYZFF1c3UZfwxjjH0vfC1SuhVH4JCFd1\n" +
"fCkDByiJCtQ3YMPSd5Z78FGdaBb0bEFTbQpxHxIjQOVvTsDYSrQ8fiot5LAE+TAd\n" +
"e72te4/QOaxd0AH2FUtW6izEF7cUr4dD4/+mIDz/AoGAF8efvQ4qNpR0T73t36BV\n" +
"ei/owergRfXkX6zh5GLF1BZGItNGGVvaC/aSx41yc3f7KKDMjiguhyaAeRQMHBsx\n" +
"wSusgZi+dJO0l9DhiFhhY4Tjc0lkG6dWA04DRb+GfPJUz089khwsD6XPHdj8qGtD\n" +
"jOcqr698x0tBDaCn7UUJaaA=\n" +
"-----END PRIVATE KEY-----\n";

qz.security.setSignatureAlgorithm("SHA512"); // Since 2.1
qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        try {
            var pk = KEYUTIL.getKey(privateKey);
            var sig = new KJUR.crypto.Signature({"alg": "SHA512withRSA"});  // Use "SHA1withRSA" for QZ Tray 2.0 and older
            sig.init(pk); 
            sig.updateString(toSign);
            var hex = sig.sign();
            console.log("DEBUG: \n\n" + stob64(hextorstr(hex)));
            resolve(stob64(hextorstr(hex)));
        } catch (err) {
            console.error(err);
            reject(err);
        }
    };
});
