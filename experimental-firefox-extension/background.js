// https://extensionworkshop.com/documentation/develop/temporary-installation-in-firefox/
function makeId(length) {
   var result           = '';
   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
   var charactersLength = characters.length;
   for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

function logURL(requestDetails) {
  console.log("Redirecting: " + requestDetails.url);
  var parts;
  if (requestDetails.url.startsWith('https://www.riteaid.com/services/ext/v2/vaccine/checkSlots')) {
      parts = requestDetails.url.split('=');
      var storeNumber = parts[parts.length - 1];
      var id = makeId(20);
      return {
        redirectUrl: 'https://www.riteaid.com/services/ext/v2/vaccine/checkSlots?foo=' + id + '&storeNumber=' + storeNumber
      };
  } else {
    parts = requestDetails.url.split('?');
    var new_url = parts[0] + '?foo=' + makeId(20) + '&' + parts[1];
      return {
        redirectUrl: new_url
      };
  }

}

browser.webRequest.onBeforeRequest.addListener(
  logURL,
  {urls: [
    "https://www.riteaid.com/services/ext/v2/vaccine/checkSlots?storeNumber=*",
    'https://www.riteaid.com/content/riteaid-web/en.ragetavailableappointmentslots.json?storeNumber=*'
   ]},
  ["blocking"]
);

