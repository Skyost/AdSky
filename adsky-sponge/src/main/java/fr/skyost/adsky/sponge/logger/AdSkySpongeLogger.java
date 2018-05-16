package fr.skyost.adsky.sponge.logger;

import fr.skyost.adsky.core.AdSkyLogger;
import org.slf4j.Logger;

/**
 * Default plugin logger.
 */

public class AdSkySpongeLogger implements AdSkyLogger {

	/**
	 * The plugin's logger instance.
	 */

	private final Logger logger;

	/**
	 * Creates a new logger instance.
	 *
	 * @param logger The plugin's logger.
	 */

	public AdSkySpongeLogger(final Logger logger) {
		this.logger = logger;
	}

	@Override
	public final void message(final String message) {
		log(message);
	}

	@Override
	public final void error(final String message) {
		logger.error(message);
	}

	@Override
	public final void error(final String message, final Throwable throwable) {
		logger.error(message, throwable);
	}

	@Override
	public final void success(final String message) {
		logger.info(message);
	}

	@Override
	public final void log(final String message) {
		logger.info(message);
	}

}