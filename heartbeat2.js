/**
 * Heartbeat2 based on Wordpress heartbeat.
 *
 * Heartbeat API
 *
 * Available PHP filters:
 * - heartbeat2_received
 * - heartbeat2_tick
 *
 * Custom jQuery events:
 * - heartbeat2-send
 * - heartbeat2-tick
 * - heartbeat2-error
 * - heartbeat2-connection-restored
 *
 */

( function( $, window, undefined ) {

	/**
	 * Constructs the Heartbeat API.
	 */
	var Heartbeat2 = function() {
		var $document = $(document),
			settings = {
				// XHR request URL, defaults to the JS global 'ajaxurl' when present.
				url: '',

				// Timestamp, start of the last connection request.
				lastTick: 0,

				// Container for the enqueued items.
				queue: {},

				// Connect interval (in seconds).
				mainInterval: 60,

				// Whether a connection is currently in progress.
				connecting: false,

				// Whether a connection error occurred.
				connectionError: false,

				// Whether at least one connection has been completed successfully.
				hasConnected: false,

				// Timer that keeps track of how long needs to be waited before connecting to
				// the server again.
				beatTimer: 0
			};

		/**
		 * Sets local variables and events, then starts the heartbeat.
		 */
		function initialize() {
			var options;

			if ( typeof window.ajaxurl === 'string' ) {
				settings.url = window.ajaxurl;
			}

			// Pull in options passed from PHP.
			if ( typeof window.heartbeat2Settings === 'object' ) {
				options = window.heartbeat2Settings;

				// The XHR URL can be passed as option when window.ajaxurl is not set.
				if ( ! settings.url && options.ajaxurl ) {
					settings.url = options.ajaxurl;
				}

				if ( options.interval ) {
					settings.mainInterval = options.interval;

					if ( settings.mainInterval < 15 ) {
						settings.mainInterval = 15;
					} else if ( settings.mainInterval > 9999 ) {
						settings.mainInterval = 9999;
					}
				}
			}

			// Convert to milliseconds.
			settings.mainInterval *= 1000;

			// Start one tick after DOM ready.
			$document.ready( function() {
				settings.lastTick = time();
				scheduleNextTick();
			});
		}

		/**
		 * Returns the current time according to the browser.
		 */
		function time() {
			return (new Date()).getTime();
		}

		function hasConnectionError() {
			return settings.connectionError;
		}

		/**
		 * Sets error state and fires an event on XHR errors or timeout.
		 */
		function setErrorState( error, status ) {
			if ( error ) {
				settings.connectionError = true;
			}
		}

		/**
		 * Clears the error state and fires an event if there is a connection error.
		 */
		function clearErrorState() {
			// Has connected successfully.
			settings.hasConnected = true;

			if ( hasConnectionError() ) {
				settings.connectionError = false;
				$document.trigger( 'heartbeat2-connection-restored' );
			}
		}

		/**
		 * Gathers the data and connects to the server.
		 */
		function connect() {
			var ajaxData, heartbeatData;

			if ( settings.connecting ) {
				return;
			}

			settings.lastTick = time();

			heartbeatData = $.extend( {}, settings.queue );
			// Clear the data queue. Anything added after this point will be sent on the next tick.
			settings.queue = {};

			$document.trigger( 'heartbeat2-send', [ heartbeatData ] );

			ajaxData = {
				data: heartbeatData,
				interval: settings.mainInterval / 1000,
				_nonce: typeof window.heartbeat2Settings === 'object' ?
					window.heartbeat2Settings.nonce : '',
				action: 'heartbeat2',
			};

			settings.connecting = true;
			settings.xhr = $.ajax({
				url: settings.url,
				type: 'post',
				timeout: 30000, // throw an error if not completed after 30 sec.
				data: ajaxData,
				dataType: 'json'
			}).always( function() {
				settings.connecting = false;
				scheduleNextTick();
			}).done( function( response, textStatus, jqXHR ) {
				var newInterval;

				if ( ! response ) {
					setErrorState( 'empty' );
					return;
				}

				clearErrorState();

				// Update the heartbeat nonce if set.
				if ( response.heartbeat_nonce && typeof window.heartbeat2Settings === 'object' ) {
					window.heartbeat2Settings.nonce = response.heartbeat_nonce;
					delete response.heartbeat_nonce;
				}

				$document.trigger( 'heartbeat2-tick', [response, textStatus, jqXHR] );
			}).fail( function( jqXHR, textStatus, error ) {
				setErrorState( textStatus || 'unknown', jqXHR.status );
				$document.trigger( 'heartbeat2-error', [jqXHR, textStatus, error] );
			});
		}

		/**
		 * Schedules the next connection.
		 *
		 * Fires immediately if the connection time is longer than the interval.
		 */
		function scheduleNextTick() {
			var delta = time() - settings.lastTick,
			    interval = settings.mainInterval;

			window.clearTimeout( settings.beatTimer );

			if ( delta < interval ) {
				settings.beatTimer = window.setTimeout(
					function() {
						connect();
					},
					interval - delta
				);
			} else {
				connect();
			}
		}

		/**
		 * Connects as soon as possible.
		 *
		 * Will not open two concurrent connections. If a connection is in progress,
		 * will connect again immediately after the current connection completes.
		 */
		function connectNow() {
			settings.lastTick = 0;
			scheduleNextTick();
		}

		/**
		 * Enqueues data to send with the next XHR.
		 */
		function enqueue( handle, data, noOverwrite ) {
			if ( handle ) {
				if ( noOverwrite && this.isQueued( handle ) ) {
					return false;
				}

				settings.queue[handle] = data;
				return true;
			}
			return false;
		}

		/**
		 * Checks if data with a particular handle is queued.
		 */
		function isQueued( handle ) {
			if ( handle ) {
				return settings.queue.hasOwnProperty( handle );
			}
		}

		/**
		 * Removes data with a particular handle from the queue.
		 */
		function dequeue( handle ) {
			if ( handle ) {
				delete settings.queue[handle];
			}
		}

		/**
		 * Gets data that was enqueued with a particular handle.
		 */
		function getQueuedItem( handle ) {
			if ( handle ) {
				return this.isQueued( handle ) ? settings.queue[handle] : undefined;
			}
		}

		initialize();

		/* Expose public methods. */
		return {
			connectNow: connectNow,
			hasConnectionError: hasConnectionError,
			enqueue: enqueue,
			dequeue: dequeue,
			isQueued: isQueued,
			getQueuedItem: getQueuedItem
		};
	};

	window.heartbeat2 = new Heartbeat2();

}( jQuery, window ));
