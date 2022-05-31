/* globals Chart:false, feather:false */

(function () {
    'use strict'
    if(window.localStorage.getItem('token') == undefined) window.location = 'sign-in'
    else window.location = 'dashboard'
})()
  