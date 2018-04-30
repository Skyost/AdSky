package fr.skyost.adsky.core.utils;

import java.util.Calendar;

/**
 * Utilities methods.
 */

public class Utils {

	/**
	 * Gets a Calendar instance of tomorrow midnight.
	 *
	 * @return A Calendar instance of tomorrow midnight.
	 */

	public static Calendar tomorrowMidnight() {
		final Calendar tomorrowMidnight = Calendar.getInstance();
		tomorrowMidnight.add(Calendar.MILLISECOND, 24 * 60 * 60 * 1000);
		tomorrowMidnight.set(Calendar.HOUR_OF_DAY, 0);
		tomorrowMidnight.set(Calendar.MINUTE, 0);
		tomorrowMidnight.set(Calendar.SECOND, 1);
		tomorrowMidnight.set(Calendar.MILLISECOND, 0);

		return tomorrowMidnight;
	}

}