<a href="/<SLUG>/new" id="<SLUG>-new">New +</a>
<table>
   <tr>
      <th>field</th>
      <th></th>
      <th></th>
   </tr>
   <?php foreach ($<DATABASE_NAME> as $row): ?>
   <tr>
      <td><?= $row['field'] ?></td>
      <td><a href="/<SLUG>/edit?_id=<?= $row['_id'] ?>">edit</a></td>
      <td><a class="<SLUG>-delete" href="#" <DATABASE_NAME>_rev="<?=$row['_rev']?>" <DATABASE_NAME>_id="<?=$row['_id']?>">delete</a></td>
   </tr>
   <?php endforeach ?>
</table>
<script type="text/javascript">
zs.util.require("<SLUG>/main");
</script>
