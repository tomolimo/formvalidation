/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016-2023 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Form Validation plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

var FVH = {
  //------------------------------------------
  // helper function to verify if a string
  // is really a date
  //------------------------------------------
  isValidDate: function (str) {
    try {
      if (str.length == 0) {
        return false;
      }
      flatpickr.parseDate(str, "Y-m-d H:i:S");
      return true;
    } catch (e) {
      return false;
    }
  },

  //------------------------------------------
  // helper function to verify if a string
  // is really a time from 00:00[:00] to 23:59[:59]
  //------------------------------------------
  isValidTime: function (str) {
    return /^(?:[0-1]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/.test(str);
  },

  //------------------------------------------
  // helper function to verify if a string
  // is really an integer
  //------------------------------------------
  isValidInteger: function (str) {
    return /^\d+$/.test(str);
  },

  //------------------------------------------
  // helper function to count words in given string
  // returns quantity of words
  //------------------------------------------
  countWords: function (str) {
    return str.split(/\W+/).length;
  },

  //------------------------------------------
  // helper function to verify if a string
  // is really an IPV4 address
  // uses the datapicker JQuery plugin
  //------------------------------------------
  isValidIPv4: function (ipaddress) {
    return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(
      ipaddress
    );
  },

  //------------------------------------------
  // helper function to verify if a string
  // is really an IPV6 address
  // uses the datapicker JQuery plugin
  //------------------------------------------
  isValidIPv6: function (ipaddress) {
    return /^((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*::((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*|((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4})){7}$/.test(
      ipaddress
    );
  },

  //------------------------------------------
  // helper function to verify if a string
  // is really an email address
  // will use the input type=email if it exists (HTML5)
  // otherwise will use a basic verification.
  //------------------------------------------
  isValidEmail: function (str) {
    var input = document.createElement("input");

    input.type = "email";
    input.value = str;

    return typeof input.checkValidity == "function"
      ? input.checkValidity()
      : /^\S+@\S+\.\S+$/.test(str);
   },

   //------------------------------------------
   // helper function to verify if a string
   // is really an URL
   //------------------------------------------
   isValidURL: function (str) {
      return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/i.test(str);
   },

  //------------------------------------------
  // helper function to verify if a string
  // is really a MAC address
  //------------------------------------------
  isValidMacAddress: function (str) {
    return /^[\da-f]{2}([:-])(?:[\da-f]{2}\1){4}[\da-f]{2}$/i.test(str);
  },
};
