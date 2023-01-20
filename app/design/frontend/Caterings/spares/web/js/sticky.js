/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define( [
    'jquery',
    'matchMedia',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'jquery-ui-modules/effect',
], function ( $, mediaCheck ) {
    'use strict';
    var timer;
    var showtime;
    function debouncer( func, timeout ) {
        var timeoutID, timeout = timeout || 500;
        return function () {
            var scope = this,
                args = arguments;
            clearTimeout( timeoutID );
            timeoutID = setTimeout( function () {
                func.apply( scope, Array.prototype.slice.call( args ) );
            }, timeout );
        }
    }
    ;
    $.widget( 'mage.OXsticky', {
        options: {
            stickyTarget: null,
            scrollStart: null,
            defaultClass: 'ox-sticky',
            stickyClass: 'sticky',
            scrollUpShow: false,
            scrollUpClass: 'sticky-scroll-up',
            scrollDownShow: false,
            scrollDownClass: 'sticky-scroll-down',
            scrollSmartStart: 'height',
            scrollSmart: null,
            maxHeight: null,
            addMargin: false,
            margin: 0,
            marginTarget: null,
            timeout: 0,
            interval: 10,
            mediaBreakpoint: '(min-width: 1025px)',
            searchOver: false,
            resizedHeight: 64
        },
        _create: function () {
            if ( null === this.options.scrollSmart ) {
                this.options.scrollSmart = $( 'body' ).hasClass( 'sticky-smart' );
            }
            this.status = null;
            mediaCheck( {
                media: this.options.mediaBreakpoint,
                entry: $.proxy( function () {
                    this.minimize = true
                }, this ),
                exit: $.proxy( function () {
                    this.minimize = false
                }, this ),
            } );
            if ( this.options.scrollSmart ) {
                this.options.scrollDownShow = this.options.scrollUpShow = true;
            }
            if ( this.options.stickyTarget ) {
                this.target = this.element.find( this.options.stickyTarget );
            } else {
                this.target = this.element;
            }
            if ( !this.target.length ) {
                this.target = this.element;
            }
            this.element.trigger( 'initsticky.ox' );
            this._updateHeight();
            if ( this.options.addMargin ) {
                if ( this.options.marginTarget ) {
                    this.marginTarget = $( this.options.marginTarget );
                } else {
                    this.marginTarget = this.element.next();
                }
                this.marginTarget.data( 'margin-top', this.marginTarget.css( 'margin-top' ) );
                this.margin_outerHeight = this.target.outerHeight();
            }
            this.margin__outerHeight = 0;
            if ( this.options.defaultClass ) {
                this.target.addClass( this.options.defaultClass );
            }

            if ( !this.options.scrollStart ) {
                this.options.scrollStart = this.target.offset().top;
            }
            if ( !this.options.scrollSmartStart ) {
                this.options.scrollSmartStart = 0;
            }
            if ( 'height' == this.options.scrollStart ) {
                this.options.scrollStart = this.target.outerHeight();
            }
            this.lastPosition = $( window ).scrollTop();
            this.lastSmartPosition = 0;
            if ( this.options.scrollUpShow ) {
                this._mouseLeave( this.target );
                this._mouseEnter( this.target );
            }
            this.delt_minimize = this.target.children( '.sticky-wrapper' ).height() - this.options.resizedHeight;

            var _self = this;
            $( window ).on( 'scroll', function () {
                debouncer( _self.refresh(), _self.options.interval );
            } );
            var win_size = $( window ).innerWidth();
            $( window ).on( 'resize', function () {
                var new_size = $( window ).innerWidth();
                if ( win_size == new_size ) {
                    return;
                }
                debouncer( _self._updateHeight() );
                setTimeout( function () {
                    debouncer( _self._updateHeight() );
                }, 310 );
            } );

            if ( $( 'body.sticky-minimized.header--layout-1' ).length ) {
                this._toggleSearch();
            }
            this.refresh();
            this.element.trigger( 'initedsticky.ox' );

        },
        hide: function () {
            this.target.removeClass( this.options.scrollUpClass ).removeClass( this.options.scrollDownClass );
            this.element.trigger( 'hidesticky.ox' );
        },
        showUp: function ( status ) {
            if ( !this.options.scrollUpShow )
                return;
            this.target.toggleClass( this.options.scrollUpClass, status );
            this.element.trigger( 'upsticky.ox' );
            this._resetTimer();
        },
        showDown: function ( status ) {
            if ( !this.options.scrollDownShow )
                return;
            this.target.toggleClass( this.options.scrollDownClass, status )
            this.element.trigger( 'downsticky.ox' );
            this._resetTimer();
        },
        addMargin: function ( status ) {
            if ( !this.options.addMargin )
                return;
            var margin = 0;
            if ( status ) {
                if ( this.options.margin ) {
                    margin = this.options.margin;
                } else {
                    if ( !this.margin__outerHeight && !status ) {
                        this.margin__outerHeight = this.target.outerHeight();
                    }
                    margin = this.margin__outerHeight ? this.margin__outerHeight : this.margin_outerHeight;
                }
                this.marginTarget.css( 'margin-top', margin );
            } else {
                margin = this.marginTarget.data( 'margin-top' );
                if ( margin ) {
                    this.marginTarget.css( 'margin-top', margin );
                } else {
                    this.marginTarget.css( 'margin-top', null );
                }
            }
        },
        refresh: function () {
            var scrollPosition = $( window ).scrollTop();
            var direction = scrollPosition - this.lastPosition;
            /*
             if (0 === direction) {
             return;
             }
             */
            this.lastPosition = scrollPosition;
            var minimizeEnabled = $( 'body.sticky-minimized.header--layout-1' ).length;
            var scroll_start = this.options.scrollSmart;
            if ( this.options.scrollSmart && minimizeEnabled ) {
                scroll_start += parseFloat( ( this.target.css( 'min-height' ) || '' ).replace( /[^0-9\.,]+/ig, '' ) || this.target.height() );
            }
            var status = scroll_start < scrollPosition;
            if ( 0 > direction ) {
                scroll_start = /*minimizeEnabled ? this.delt_minimize :*/ 0;
                status = scroll_start < scrollPosition;
            }
            // Margin
            this.addMargin( status );
            // Sticky Class
            this.target.toggleClass( this.options.stickyClass, status );

            // Smart Scroll
            if ( 0 > direction ) {
                this.lastSmartPosition = scrollPosition;
            }

            var status_minimize = this.options.scrollStart + this.delt_minimize < scrollPosition;
            if ( this.options.scrollSmart && minimizeEnabled && 0 < direction ) {
                status_minimize = this.options.scrollStart + scroll_start < scrollPosition;
            }
            if ( minimizeEnabled && !window.matchMedia( '(max-width: 1024px)' ).matches ) {
                this._hideSearch( status );
            }
            if ( status ) {
                var scrollStart = ( 'height' == this.options.scrollSmartStart ) ? this.target.height() : parseInt( this.options.scrollSmartStart );
                if ( 0 < scrollStart ) {
                    this.showUp( 0 > direction );
                    this.showDown( 0 < direction && scrollStart < scrollPosition );
                } else {
                    this.showUp( 0 > direction );
                    this.showDown( 0 < direction );
                }
                this.element.trigger( 'setsticky.ox' );
            } else {
                this.hide();
                this.element.trigger( 'removesticky.ox' );
            }
            if ( this.options.scrollSmart ) {
                var _status = this.options.scrollUpShow && ( 0 > direction && ( ( 0 >= direction && minimizeEnabled ) ? this.delt_minimize < scrollPosition : status ) );
                if ( this.status != _status ) {
                    if ( _status ) {
                        this._updateHeight_logo( _status );
                    } else {
                        setTimeout( $.proxy( function () {
                            this.target.children( '.sticky-wrapper' ).css( { 'transform': '' } );
                        }, this ), 50 );
                        /* this is wrong! it changes header from 64 to 147px and makes content JUMP! */
                        /*if ( !status ) {
                         this._updateHeight();
                         }*/
                    }
                }
                this.status = _status;

            } else {
                if ( this.status != status ) {
                    this._updateHeight_logo();
                }
                this.status = status;
            }
            if ( minimizeEnabled && this.minimize ) {
                if ( status_minimize ) {
                    this._stickyMinimize();
                } else {
                    this._stickyMaximize();
                }
            }
            if ( minimizeEnabled ) {
                this.target.toggleClass( 'resize', status_minimize );
            }
        },
        _removeTimer: function () {
            if ( timer ) {
                clearTimeout( timer );
            }
        },
        _resetTimer: function () {
            var _self = this;
            _self._removeTimer();
            if ( _self.options.timeout ) {
                timer = setTimeout( function () {
                    _self.hide();
                }, _self.options.timeout );
            }

        },
        _mouseLeave: function ( handler ) {
            var _self = this;
            handler.on( 'mouseleave', function ( event ) {
                event.stopPropagation();
                _self._resetTimer();
            } );
        },
        _mouseEnter: function ( handler ) {
            var _self = this;
            handler.on( 'mouseenter', function ( event ) {
                event.stopPropagation();
                _self._removeTimer();
            } );
        },
        _updateHeight: function () {
            var topbar = this.target.children( '.sticky-wrapper' ).find( '.top-bar' );
            var topbar_height = topbar.height();
            topbar.css( 'height', topbar_height );
            this.target.css( 'min-height', '' );
            var height = this.target.children( '.sticky-wrapper' ).height();
            if ( 0 < height ) {
                this.target.css( 'min-height', height );
            }

        },
        _updateHeight_logo: function ( _status ) {
            if ( window.matchMedia( '(max-width: 1025px)' ).matches && $( 'body.sticky-minimized.mobile-header--layout-2' ).length && this.target.hasClass( this.options.stickyClass ) ) {
                /*this.target.css('min-height', '');*/
                var height = this.target.children( '.sticky-wrapper' ).height();
                var $logo = this.target.find( '.logo__container' );
                if ( $logo.length ) {
                    var logo_height = $logo.outerHeight();
                    height -= logo_height;
                    this.target.children( '.sticky-wrapper' ).css( { 'transform': 'translateY(-' + logo_height + 'px)' } );
                }
                if ( 0 < height && !_status ) {
                    this.target.css( 'min-height', height );
                }
            } else {
                this.target.children( '.sticky-wrapper' ).css( { 'transform': '' } );
            }
        },
        _stickyMinimize: function () {

            $( '.ox-move-sticky' ).each( $.proxy( function ( index, item ) {
                var $this = $( item ),
                    _class = ( ( $this.attr( 'class' ) || '' ).match( /ox-move-sticky-([^ ]{1,})/i ) || [ '', '' ] )[1],
                    $sticky_parent = $( '[data-move-sticky="' + _class + '"]' ).eq( 0 );
                if ( !_class || !$sticky_parent.length || $this.parent().is( $sticky_parent ) ) {
                    return;
                }
                if ( !$( '[data-move-back="' + _class + '"]' ).length ) {
                    $this.parent().attr( 'data-move-back', _class );
                }
                $this.data( 'moveBackPosition', $this.parent().children().index( $this ) );
                var element = $this.detach();
                $sticky_parent.append( element );
            }, this ) );
        },
        /**
         * @private
         */
        _stickyMaximize: function () {
            $( '.ox-move-sticky' ).each( $.proxy( function ( index, item ) {
                var $this = $( item ),
                    _class = ( ( $this.attr( 'class' ) || '' ).match( /ox-move-sticky-([^ ]{1,})/i ) || [ '', '' ] )[1],
                    $back_parent = $( '[data-move-back="' + _class + '"]' ).eq( 0 ),
                    position = $this.data( 'moveBackPosition' ) || 0;
                if ( !_class || !$back_parent.length || $this.parent().is( $back_parent ) ) {
                    return;
                }
                var element = $this.detach();
                if ( 0 < position ) {
                    var prev = $back_parent.children().eq( position - 1 );

                    if ( prev.length ) {
                        prev.after( element );
                    } else {
                        $back_parent.prepend( element );
                    }
                } else {
                    $back_parent.prepend( element );
                }
            }, this ) );
        },
        _hideSearch: function ( status ) {
            this.options.searchOver = false;
            var search_mini_form = this.target.find( '.block-search--type-panel' ).find( '#search_mini_form' ).removeClass( 'form-search-over' );
            if ( status ) {
                search_mini_form.find( '#search' ).css( { 'opacity': 0 } );
            } else {
                search_mini_form.find( '#search' ).removeAttr( 'style' );
            }
        },
        _toggleSearch: function () {
            var _this = this;
            var search_mini_form = this.target.find( '.block-search--type-panel' ).find( '#search_mini_form' );
            if ( search_mini_form.length ) {
                search_mini_form.on( 'click', function ( event ) {
                    if ( window.matchMedia( '(max-width: 1025px)' ).matches || $( 'body.ox-slideout-active' ).length ) {
                        return
                    }
                    event.stopPropagation();
                    var $this = $( this );
                    if ( _this.options.searchOver ) {
                        return true;
                    }

                    $this.addClass( 'form-search-over' );
                    search_mini_form.find( '#search' ).addClass( 'animate' );
                    search_mini_form.find( '#search' ).stop( true, false ).css( 'opacity', 0 ).animate( { opacity: 1 }, 400, 'easeOutExpo', function () {
                        _this.options.searchOver = true;
                    } );
                    return false;

                } );
                //Hide search if visible
                $( 'body' ).on( 'click', function ( event ) {
                    if ( window.matchMedia( '(max-width: 1025px)' ).matches || $( 'body.ox-slideout-active' ).length || _this.options.scrollStart + _this.delt_minimize >= $( window ).scrollTop() ) {
                        return
                    }
                    if ( _this.options.searchOver ) {
                        _this.options.searchOver = false;
                        search_mini_form.find( '#search' ).removeClass( 'animate' );
                        search_mini_form.find( '#search' ).stop( true, false ).animate( { opacity: 0 }, 400, 'easeInExpo', function () {
                            search_mini_form.removeClass( 'form-search-over' );
                        } );
                    }

                } );
                search_mini_form.find( '#search' ).on( "touchend", function ( e ) {
                    e.stopPropagation();
                } );
            }
        }
    } );
} );