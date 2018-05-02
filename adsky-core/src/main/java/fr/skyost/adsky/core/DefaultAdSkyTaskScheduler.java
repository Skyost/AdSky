package fr.skyost.adsky.core;

import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

/**
 * Default AdSky task scheduler, using Executors.
 */

public class DefaultAdSkyTaskScheduler implements AdSkyTaskScheduler {

	@Override
	public void schedule(final Runnable task, final long delay) {
		Executors.newScheduledThreadPool(1).schedule(task, delay < 0 ? 0 : delay, TimeUnit.MILLISECONDS);
	}

}