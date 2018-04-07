package fr.skyost.adsky.utils;

import java.util.Calendar;

public class Utils {

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