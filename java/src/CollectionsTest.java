import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;


public class CollectionsTest {

	public static <F,T> List<T> map(List<F> list, Function1<F,T> func) {
		List<T> res = new ArrayList<T>();
		for (F f : list)
			res.add(func.call(f));
		return res;
	}
	
	public static void main(String[] args) {
		
		List<String> l = Arrays.asList(new String[] {"10","20","30","40"});
		
		List<Integer> l2 = map(l, new Function1<String, Integer>() {
			@Override
			public Integer call(String arg) {
				return Integer.valueOf(arg);
			}
		}); 
		
		for (Integer is : l2) {
			System.out.println(is*2);
		}
		
	}

}
