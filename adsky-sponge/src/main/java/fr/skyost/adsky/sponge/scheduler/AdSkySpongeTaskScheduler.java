package fr.skyost.adsky.sponge.scheduler;

import fr.skyost.adsky.core.AdSkyTaskScheduler;
import fr.skyost.adsky.sponge.AdSkySpongePlugin;
import org.spongepowered.api.scheduler.Task;

import java.util.concurrent.TimeUnit;

public class AdSkySpongeTaskScheduler implements AdSkyTaskScheduler {

	private final AdSkySpongePlugin plugin;

	public AdSkySpongeTaskScheduler(final AdSkySpongePlugin plugin) {
		this.plugin = plugin;
	}

	@Override
	public final void schedule(final Runnable task, final long delay) {
		Task.builder().delay(delay, TimeUnit.MILLISECONDS).async().execute(task).submit(plugin);
	}

}