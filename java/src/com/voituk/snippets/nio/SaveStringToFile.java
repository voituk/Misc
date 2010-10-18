package com.voituk.snippets.nio;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.nio.ByteBuffer;
import java.nio.channels.FileChannel;

public class SaveStringToFile {

	/**
	 * @param args
	 */
	public static void main(String[] args) throws IOException {
		
		String body = "Привет_МИР_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_1234567890_THE_END!!!_";
		System.out.println("body.length="+body.length());
		
		File file = new File(SaveStringToFile.class.getSimpleName() + ".txt");
		
		FileOutputStream os = null;
		FileChannel ch      = null;
		try 
		{
			byte[] src = body.getBytes("UTF-8");
			System.out.println("src.length="+src.length);
			final int block = 1024;
			os = new FileOutputStream(file);
			ch = os.getChannel();
			ByteBuffer buff = ByteBuffer.allocate(block);
			
			int written = 0;
			int length = src.length;
			while (written < length) {
				System.out.println( written + " .. " +  Math.min(block, length-written) );
				written += ch.write((ByteBuffer)buff.put(src, written, Math.min(block, length-written) ).flip());
				buff.rewind();
			}
			
		} 
		finally 
		{
			if (ch!=null) ch.close();
			if (os!=null) os.close();
		}
		
		assert file.length() == body.getBytes().length : "File corrupted!"; 
	}

}
