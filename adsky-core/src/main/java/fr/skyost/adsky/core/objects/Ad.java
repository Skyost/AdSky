package fr.skyost.adsky.core.objects;

import com.udojava.evalex.Expression;
import fr.skyost.adsky.core.AdSkyConfiguration;

import java.math.BigDecimal;
import java.math.RoundingMode;

/**
 * Represents an AdSky Ad.
 */

public abstract class Ad {

	/**
	 * The Title ad type.
	 */

	public static final int TYPE_TITLE = 0;

	/**
	 * The Chat ad type.
	 */

	public static final int TYPE_CHAT = 1;

	/**
	 * Ad's username.
	 */

	private String username;

	/**
	 * Ad's type.
	 */

	private int type;

	/**
	 * Ad's title.
	 */

	private String title;

	/**
	 * Ad's message.
	 */

	private String message;

	/**
	 * Ad's interval.
	 */

	private int interval;

	/**
	 * Ad's expiration.
	 */

	private long expiration;

	/**
	 * Ad's duration.
	 */

	private int duration;

	/**
	 * Creates a new Ad instance.
	 *
	 * @param username The username.
	 * @param type The type (see Ad constants).
	 * @param title The title.
	 * @param message The message.
	 * @param interval The interval.
	 * @param expiration The expiration.
	 * @param duration The duration.
	 */

	protected Ad(final String username, final int type, final String title, final String message, final int interval, final long expiration, final int duration) {
		this.username = username;
		this.type = type;
		this.title = title;
		this.message = message;
		this.interval = interval;
		this.expiration = expiration;
		this.duration = duration;
	}

	/**
	 * Gets the username.
	 *
	 * @return The username.
	 */

	public final String getUsername() {
		return username;
	}

	/**
	 * Sets the username.
	 *
	 * @param username The username.
	 */

	public final void setUsername(final String username) {
		this.username = username;
	}

	/**
	 * Gets the type (see Ad constants).
	 *
	 * @return The type.
	 */

	public final int getType() {
		return type;
	}

	/**
	 * Checks if this Ad is a Title Ad.
	 *
	 * @return Whether this Ad is a Title Ad.
	 */

	public final boolean isTitleAd() {
		return type == TYPE_TITLE;
	}

	/**
	 * Checks if this Ad is a Chat Ad.
	 *
	 * @return Whether this Ad is a Chat Ad.
	 */

	public final boolean isChatAd() {
		return !isTitleAd();
	}

	/**
	 * Sets the type of this Ad.
	 *
	 * @param type The type.
	 */

	public final void setType(final int type) {
		this.type = type;
	}

	/**
	 * Gets the title.
	 *
	 * @return The title.
	 */

	public final String getTitle() {
		return title;
	}

	/**
	 * Sets the title of this Ad.
	 *
	 * @param title The title.
	 */

	public final void setTitle(final String title) {
		this.title = title;
	}

	/**
	 * Gets the message.
	 *
	 * @return The message.
	 */

	public final String getMessage() {
		return message;
	}

	/**
	 * Sets the message of this Ad.
	 *
	 * @param message The message.
	 */

	public final void setMessage(final String message) {
		this.message = message;
	}

	/**
	 * Gets the interval.
	 *
	 * @return The interval.
	 */

	public final int getInterval() {
		return interval;
	}

	/**
	 * Sets the interval of this Ad.
	 *
	 * @param interval The interval.
	 */

	public final void setInterval(final int interval) {
		this.interval = interval;
	}

	/**
	 * Gets the expiration.
	 *
	 * @return The expiration.
	 */

	public final long getExpiration() {
		return expiration;
	}

	/**
	 * Sets the expiration of this Ad.
	 *
	 * @param expiration The expiration.
	 */

	public final void setExpiration(final long expiration) {
		this.expiration = expiration;
	}

	/**
	 * Gets the duration.
	 *
	 * @return The duration.
	 */

	public final int getDuration() {
		return duration;
	}

	public final void setDuration(final int duration) {
		this.duration = duration;
	}

	/**
	 * Broadcasts this ad.
	 */

	public abstract void broadcast();

	/**
	 * Multiplies this ad according to the broadcast interval.
	 *
	 * @return An array containing all ads.
	 */

	public abstract Ad[] multiply();

	/**
	 * Evaluates the distribution function at the given hour.
	 *
	 * @param config The AdSky config.
	 * @param preferredHour The preferred hour.
	 * @param hour The hour.
	 * @param adsNumber Today's ads number.
	 *
	 * @return The number of ads to broadcast at the given hour.
	 */

	public static int getAdsPerHour(final AdSkyConfiguration config, final int preferredHour, final int hour, final int adsNumber) {
		final Expression expression = new Expression(config.getAdsDistributionFunction());
		final BigDecimal result = expression.with("h", new BigDecimal(preferredHour))
											.with("x", new BigDecimal(hour))
											.with("n", new BigDecimal(adsNumber))
											.eval();

		return result.setScale(0, RoundingMode.UP).intValue();
	}

}