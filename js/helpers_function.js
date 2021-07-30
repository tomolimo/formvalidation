var FVH = {
  //------------------------------------------
  // helper function to verify if a string
  // is really a date
  // uses the datepicker JQuery plugin
  //------------------------------------------
  isValidDate: function (string) {
    try {
      if (string.length == 0) {
        return false;
      }
      flatpickr.parseDate(string, "Y-m-d H:i:S");
      return true;
    } catch (e) {
      return false;
    }
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really a time from 00:00[:00] to 23:59[:59]
  //------------------------------------------
  isValidTime: function (str) {
    return /^(?:[0-1]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/.test(str);
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really an integer
  //------------------------------------------
  isValidInteger: function (str) {
    return /^\d+$/.test(str);
  },

  //------------------------------------------
  // helper function to count words in a given string
  // returns quantity of words
  //------------------------------------------
  countWords: function (str) {
    return str.split(/\W+/).length;
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really an IPV4 address
  // uses the datapicker JQuery plugin
  //------------------------------------------
  isValidIPv4: function (ipaddress) {
    return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(
      ipaddress
    );
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really an IPV6 address
  // uses the datapicker JQuery plugin
  //------------------------------------------
  isValidIPv6: function (ipaddress) {
    return /^((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*::((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*|((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4})){7}$/.test(
      ipaddress
    );
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really an email address
  // will use the input type=email if it exists (HTML5)
  // otherwise will use a basic verification.
  //------------------------------------------
  isValidEmail: function (value) {
    var input = document.createElement("input");

    input.type = "email";
    input.value = value;

    return typeof input.checkValidity == "function"
      ? input.checkValidity()
      : /^\S+@\S+\.\S+$/.test(value);
  },

  //------------------------------------------
  // helper function to verify a if a string
  // is really a MAC address
  //------------------------------------------
  isValidMacAddress: function (sftr) {
    return /^[\da-f]{2}([:-])(?:[\da-f]{2}\1){4}[\da-f]{2}$/i.test(str);
  },
};
