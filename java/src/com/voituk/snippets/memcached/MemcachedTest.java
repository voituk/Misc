package com.voituk.snippets.memcached;


import java.net.InetSocketAddress;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.atomic.AtomicLong;

import net.spy.memcached.ConnectionFactory;
import net.spy.memcached.ConnectionFactoryBuilder;
import net.spy.memcached.MemcachedClient;


public class MemcachedTest {

	private static final int workers = 20; // parrallel connections to memcached
	private static final int tests   = 5000;
	private static final int iterationsPerConnection = 10; // 6 ops per each interation hardcoded below
	
	private static final AtomicLong ops = new AtomicLong(0);
	private static final AtomicLong time = new AtomicLong(0);

	
	private static String randomKey() {
		return "id"+ ( 1298406151808l + Math.round(Math.random()*10000)); 
	}
	
	/**
	 * @param args
	 * @throws InterruptedException 
	 */
	public static void main(String[] args) throws InterruptedException {
		if (args.length<2) {
			System.out.println("Usage:\n\tjava MemcachedTest <hostname> <port>");
			System.exit(1);
		}
		
		final ConnectionFactory cf = new ConnectionFactoryBuilder()
			.setOpTimeout(1000)
			.setShouldOptimize(false)
			.build();
		
		final List<InetSocketAddress> servers = new ArrayList<InetSocketAddress>();
		servers.add(new InetSocketAddress(args[0], (int) Integer.valueOf(args[1])));
		
		
		ExecutorService pool = Executors.newFixedThreadPool(workers);
		
		for(int i=0; i<tests; i++) {
			pool.execute(new Runnable() {
				
				@Override
				public void run() {
					long start = System.currentTimeMillis();
					int n = 0;
					MemcachedClient client = null;
					try {
						client = new MemcachedClient(cf, servers);
						
						for (int i=0; i<iterationsPerConnection; i++) {
							String s = randomKey();
							String v = (String)client.get(s);
							if (v == null || v.equals(""));
								client.set(s, 0, s+i+s);
							client.get(randomKey());
							client.get(randomKey());

							//added n-prefix to prevent incrementing non-numeric values
							String incrKey = "n"+randomKey();
							client.add(incrKey, 0,  0);
							client.incr(incrKey, 1); 
							n+=6;
						}
						
					} catch (Exception e) {
						e.printStackTrace();
					}
					client.shutdown();
					
					time.addAndGet(System.currentTimeMillis() - start);
					ops.addAndGet(n);
					
					
				}
			});
		}
		
		pool.shutdown();
		
		while (!pool.isTerminated()) Thread.sleep(1000); // Welcome to Bangalore!
		
		System.out.println(  Math.round( (double)ops.get() / time.get() * 1000 ) + " ops/sec" );
	}

}
