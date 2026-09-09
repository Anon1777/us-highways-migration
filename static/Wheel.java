import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.Random;
import java.util.Scanner;

// javac .\\us-highways-migration\static\Wheel.java
// java -cp us-highways-migration Wheel

public class Wheel {
    public static void main(String[] args){
        ArrayList<String> roadName = new ArrayList<>();
        ArrayList<Double> weights = new ArrayList<>();
        double totalWeight = 0;
        try(Scanner in = new Scanner(new File("./us-highways-migration/static/weighted-roads.txt"))){
            while(in.hasNextLine()){
                String[] parts = in.nextLine().split(",");
                double weight = Double.parseDouble(parts[0]);
                weights.add(weight);
                roadName.add(parts[1]);
                totalWeight += weight;
            }
        } catch (FileNotFoundException e) {
            System.err.println("Program exited with code 1 - Could not read file.");
        }
        Random rand = new Random();
        double random = rand.nextDouble() * totalWeight;
        double cumulative = 0;
        int selectedIndex = 0;
        for(int i = 0; i < weights.size(); i++){
            cumulative += weights.get(i);
            if(random < cumulative){
                selectedIndex = i;
                break;
            }
        }
        String selectedRoad = roadName.get(selectedIndex);
        try(FileWriter fw = new FileWriter(new File("./us-highways-migration/static/wheel-results.txt"), true);
            PrintWriter out = new PrintWriter(fw)
        ){
            out.println(selectedRoad);
            removeRolledRoad(selectedRoad);
            System.out.println("Program exited with code 0 - Successful spin.");
        } catch (IOException e) {
            System.err.println("Program exited with code 2 - Could not write to file.");
        }
    }

    private static void removeRolledRoad(String road) throws IOException {
        File roadsFile = new File("./us-highways-migration/static/roads.txt");
        ArrayList<String> roads = new ArrayList<>();
        try (Scanner in = new Scanner(roadsFile)) {
            while (in.hasNextLine()) {
                roads.add(in.nextLine());
            }
        } catch (FileNotFoundException e) {
            return;
        }

        roads.removeIf(road::equals);

        try (PrintWriter out = new PrintWriter(new FileWriter(roadsFile, false))) {
            for (String line : roads) {
                out.println(line);
            }
        }
    }
}