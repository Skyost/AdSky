package fr.skyost.adsky.core;

/**
 * Represents an AdSky logger.
 */

public interface AdSkyLogger {

	/**
	 * Logs a simple message.
	 *
	 * @param message The message.
	 */

	void message(final String message);

	/**
	 * Logs an error.
	 *
	 * @param message The error message.
	 */

	void error(final String message);

	/**
	 * Logs an error with its stack trace.
	 *
	 * @param message The message.
	 * @param throwable The throwable.
	 */

	void error(final String message, final Throwable throwable);

	/**
	 * Logs a success.
	 *
	 * @param message The success message.
	 */

	void success(final String message);

	/**
	 * Logs a message.
	 *
	 * @param message The message.
	 */

	void log(final String message);

}