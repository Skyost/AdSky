package fr.skyost.adsky.core;

/**
 * Represents an AdSky task scheduler.
 */

public interface AdSkyTaskScheduler {

	/**
	 * Schedules a given task in the specified delay.
	 *
	 * @param task The task.
	 * @param delay The delay.
	 */

	void schedule(final Runnable task, final long delay);

}